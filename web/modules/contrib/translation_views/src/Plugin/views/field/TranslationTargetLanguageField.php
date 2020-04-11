<?php

namespace Drupal\translation_views\Plugin\views\field;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\field\LanguageField;
use Drupal\views\ResultRow;
use Drupal\translation_views\TranslationViewsTargetLanguage as TargetLanguage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Target Translation langcode field.
 *
 * Defines a field handler to translate a translation language,
 * into its readable form.
 *
 * This field handler just gets a value from exposed input and show it,
 * if doesn't make any database query.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_views_target_language")
 */
class TranslationTargetLanguageField extends LanguageField implements ContainerFactoryPluginInterface {
  use TargetLanguage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * Override parent to get langcode from exposed input,
   * instead from ResultRow.
   */
  public function render(ResultRow $values) {
    $target_langcode = $this->getTargetLangcode();

    $languages = $this->options['native_language']
      ? $this->languageManager->getNativeLanguages()
      : $this->languageManager->getLanguages();

    $build['#markup'] = $languages[$target_langcode]->getName();
    $build['#cache']['contexts'][] = 'url.query_args:' . self::$targetExposedKey;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function clickSortable() {
    return FALSE;
  }

}
