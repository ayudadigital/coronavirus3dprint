<?php

namespace Drupal\translation_views\Plugin\views\field;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\content_translation\ContentTranslationManager;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\translation_views\TranslationViewsTargetLanguage as TargetLanguage;

/**
 * Provides a field that adds moderation state.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_moderation_state")
 */
class TranslationModerationState extends FieldPluginBase {

  use TargetLanguage;

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInfo;
  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ModerationInformationInterface $moderation_info,
    LanguageManagerInterface $language_manager,
    EntityTypeManager $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->moderationInfo    = $moderation_info;
    $this->languageManager   = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('content_moderation.moderation_information'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['display_name'] = ['default' => TRUE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $default_value = 'state';
    $states = [
      'state' => $this->t('Moderation State'),
      'name'  => $this->t('Machine Name'),
    ];
    if (!empty($this->options['display_name'])) {
      $default_value = $this->options['display_name'];
    }
    $form['display_name'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Formatter'),
      '#options'       => $states,
      '#required'      => TRUE,
      '#default_value' => $default_value,
    ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {

  }

  /**
   * {@inheritdoc}
   */
  public function clickSortable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if ($values->_entity->id()) {
      /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
      $entityTypeId             = $values->_entity->getEntityTypeId();
      $storage                  = $this->entityTypeManager->getStorage($entityTypeId);
      $entity                   = $storage->load($values->_entity->id());
      $target_langcode          = $this->getTargetLangcode();
      $pending_revision_enabled = ContentTranslationManager::isPendingRevisionSupportEnabled($entityTypeId);

      if (empty($target_langcode) || $target_langcode == '***LANGUAGE_site_default***') {
        $target_langcode = $this->languageManager->getCurrentLanguage()->getId();
      }

      $translation_has_revision = $storage->getLatestTranslationAffectedRevisionId($values->_entity->id(), $target_langcode);
      if ($entity && $pending_revision_enabled && $target_langcode && $translation_has_revision) {
        $latest_revision = $storage->loadRevision($translation_has_revision);
        if ($entity && $latest_revision->hasTranslation($target_langcode)) {
          $workflow          = $this->moderationInfo->getWorkflowForEntity($latest_revision);
          $translation       = $latest_revision->getTranslation($target_langcode);
          $translation_state = $translation->moderation_state->value;

          if (!empty($this->options['display_name']) && $workflow !== NULL) {
            if ($this->options['display_name'] == 'state') {
              $translation_label = $workflow->getTypePlugin()
                ->getState($translation_state)
                ->label();
              $values->{$this->field_alias} = $translation_label ? $translation_label : NULL;
            }
            else {
              $values->{$this->field_alias} = $translation_state ? $translation_state : NULL;
            }
          }
        }
      }
    }

    return parent::render($values);
  }

}
