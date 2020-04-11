<?php

namespace Drupal\translation_views\Plugin\views\field;

use Drupal\content_translation\ContentTranslationManager;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\translation_views\TranslationViewsTargetLanguage as TargetLanguage;
use Drupal\views\Plugin\views\field\EntityOperations;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders translation operations links.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_views_operations")
 */
class TranslationOperationsField extends EntityOperations {
  use TargetLanguage;

  /**
   * Current user account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  /**
   * Flag to indicate if translators_content module exists.
   *
   * @var bool
   */
  protected $translatorsModuleExists;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    EntityTypeManager $entity_type_manager,
    LanguageManagerInterface $language_manager,
    EntityRepositoryInterface $entity_repository,
    AccountProxyInterface $account
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $language_manager, $entity_repository);
    $this->currentUser             = $account;
    $this->entityTypeManager       = $entity_type_manager;
    $this->translatorsModuleExists = \Drupal::moduleHandler()->moduleExists('translators_content');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('entity.repository'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Build operation links.
   */
  public function render(ResultRow $values) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity          = $this->getEntity($values);
    $langcode_key    = $this->buildSourceEntityLangcodeKey($entity);
    $source_langcode = $values->{$langcode_key};
    $operations      = $this->getTranslationOperations($entity, $source_langcode);

    if ($this->options['destination']) {
      foreach ($operations as &$operation) {
        if (!isset($operation['query'])) {
          $operation['query'] = [];
        }
        $operation['query'] += $this->getDestinationArray();
      }
    }
    $build = [
      '#type'  => 'operations',
      '#links' => $operations,
    ];
    $build['#cache']['contexts'][] = 'url.query_args:target_language';

    return $build;
  }

  /**
   * Build value key.
   *
   * Value key based on base table,
   * and system name of langcode key (it might be differ then just 'langcode'),
   * usually table alias is [entity_type]_field_data_[langcode_key].
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Used to extract entity type info from entity.
   *
   * @return string
   *   The value key.
   */
  protected function buildSourceEntityLangcodeKey(ContentEntityInterface $entity) {
    return implode('_', [
      $this->view->storage->get('base_table'),
      $entity->getEntityType()->getKey('langcode'),
    ]);
  }

  /**
   * Operation links manager.
   *
   * Decide which links we should generate:
   * based on user permissions,
   * and entity state (has translation, is default, etc.).
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The source entity to get context for decision.
   * @param string $source_langcode
   *   The langcode of the row.
   *
   * @return array
   *   Operation links' render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getTranslationOperations(ContentEntityInterface $entity, $source_langcode) {
    $links           = [];
    $target_langcode = $this->getTargetLangcode()
      ? $this->getTargetLangcode()
      : $source_langcode;
    $target_language = $this->languageManager->getLanguage($target_langcode);

    /* @var \Drupal\content_translation\ContentTranslationHandlerInterface $handler */
    $handler = $this->getEntityTypeManager()
      ->getHandler($entity->getEntityTypeId(), 'translation');
    $is_default = $entity->getUntranslated()->language()->getId() === $target_langcode ? TRUE : FALSE;

    // Build edit & delete link.
    if (array_key_exists($target_langcode, $entity->getTranslationLanguages())) {
      // If the user is allowed to edit the entity we point the edit link to
      // the entity form, otherwise if we are not dealing with the original
      // language we point the link to the translation form.
      if ($is_default) {
        if ($entity->access('update')
          && $entity->getEntityType()->hasLinkTemplate('edit-form')) {
          $links += $this->buildEditLink($entity, $target_langcode);
        }
        if ($entity->access('delete')
        && $entity->getEntityType()->hasLinkTemplate('delete-form')) {
          $links += $this->buildDeleteLink($entity, $target_langcode);
        }
      }
      else {
        if ($this->translatorsModuleExists) {
          if ($handler->getTranslationAccess($entity, 'update', $target_langcode)->isAllowed()) {
            $links += $this->buildEditLink($entity, $target_langcode);
          }
          if ($handler->getTranslationAccess($entity, 'delete', $target_langcode)->isAllowed()) {
            $links += $this->buildDeleteLink($entity, $target_langcode);
          }
        }
        else {
          if ($handler->getTranslationAccess($entity, 'update')->isAllowed()) {
            $links += $this->buildEditLink($entity, $target_langcode);
          }
          if ($handler->getTranslationAccess($entity, 'delete')->isAllowed()) {
            $links += $this->buildDeleteLink($entity, $target_langcode);
          }
        }
      }
    }
    // Check if there are pending revisions.
    elseif ($this->pendingRevisionExist($entity, $target_langcode)) {
      // If the user is allowed to edit the entity we point the edit link to
      // the entity form, otherwise if we are not dealing with the original
      // language we point the link to the translation form.
      if ($is_default && $entity->access('update')
        && $entity->getEntityType()->hasLinkTemplate('edit-form')) {
        $links += $this->buildEditLink($entity, $target_langcode);
      }
      else {
        if ($this->translatorsModuleExists
          && $handler->getTranslationAccess($entity, 'update', $target_langcode)->isAllowed()) {
          $links += $this->buildEditLink($entity, $target_langcode);
        }
        elseif ($handler->getTranslationAccess($entity, 'update')->isAllowed()) {
          $links += $this->buildEditLink($entity, $target_langcode);
        }
      }
    }
    // Build add link.
    elseif (!empty($target_langcode) && $entity->isTranslatable()) {
      if (!$this->translatorsModuleExists
        && $handler->getTranslationAccess($entity, 'create')->isAllowed()
      ) {
        $links += $this->buildAddLink($entity, $source_langcode, $target_langcode);
      }
      elseif ($this->translatorsModuleExists
        && $handler->getTranslationAccess($entity, 'create', $source_langcode, $target_langcode)->isAllowed()
      ) {
        $links += $this->buildAddLink($entity, $source_langcode, $target_langcode);
      }
    }

    return $links;
  }

  /**
   * Check if pending revision exist for this translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check for pending revisions.
   * @param string $target_langcode
   *   Language ID of the target language.
   *
   * @return bool
   *   TRUE - if pending revision exist, FALSE otherwise.
   */
  protected function pendingRevisionExist(ContentEntityInterface $entity, $target_langcode) {
    $entity_type_id = $entity->getEntityTypeId();
    $pending_revision_enabled = ContentTranslationManager::isPendingRevisionSupportEnabled($entity_type_id);
    if ($this->moduleHandler->moduleExists('content_moderation') && $pending_revision_enabled) {
      /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      $entity  = $storage->load($entity->id());
      $translation_has_revision = $storage->getLatestTranslationAffectedRevisionId(
        $entity->id(),
        $target_langcode
      );
      if ($translation_has_revision) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Add link builder.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to build add translation link for.
   * @param string $source_langcode
   *   Language ID of the source language.
   * @param string $target_langcode
   *   Language ID of the target language.
   *
   * @return array
   *   An array representing links.
   */
  protected function buildAddLink(ContentEntityInterface $entity, $source_langcode, $target_langcode) {
    $entity_type_id  = $entity->getEntityTypeId();
    $route_name      = "entity.$entity_type_id.content_translation_add";
    $add_url         = Url::fromRoute($route_name, [
      'source'        => $source_langcode,
      'target'        => $target_langcode,
      $entity_type_id => $entity->id(),
    ]);
    $target_language = $this->languageManager->getLanguage($target_langcode);
    $links['add']    = [
      'url'      => $add_url,
      'language' => $target_language,
      'title'    => $this->t('Add'),
    ];
    return $links;
  }

  /**
   * Delete link builder.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to build delete link for.
   * @param string $target_langcode
   *   Language ID of the target language.
   *
   * @return array
   *   An array representing links.
   */
  protected function buildDeleteLink(ContentEntityInterface $entity, $target_langcode) {
    $target_language = $this->languageManager->getLanguage($target_langcode);
    $links['delete'] = [
      'url'      => $entity->toUrl('delete-form'),
      'language' => $target_language,
      'title'    => $this->t('Delete'),
    ];
    return $links;
  }

  /**
   * Edit link builder.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to build edit link for.
   * @param string $target_langcode
   *   Language ID of the target language.
   *
   * @return array
   *   An array representing links.
   */
  protected function buildEditLink(ContentEntityInterface $entity, $target_langcode) {
    $target_language = $this->languageManager->getLanguage($target_langcode);
    $links['edit'] = [
      'url'      => $entity->toUrl('edit-form'),
      'language' => $target_language,
      'title'    => $this->t('Edit'),
    ];
    return $links;
  }

}
