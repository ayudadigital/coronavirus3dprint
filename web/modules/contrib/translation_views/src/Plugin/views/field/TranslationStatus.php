<?php

namespace Drupal\translation_views\Plugin\views\field;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TranslationStatusInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\Boolean;
use Drupal\translation_views\TranslationViewsTargetLanguage as TargetLanguage;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a field that adds translation status.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_views_status")
 */
class TranslationStatus extends Boolean implements ContainerFactoryPluginInterface {
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
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['type'] = ['default' => 'status'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    $this->definition['output formats']['status'] = [
      t('Translated'), $this->t('Not translated'),
    ];
    $this->field_alias = 'translation_status';

    parent::init($view, $display, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Convert translation status value into TranslationStatusInterface values.
   */
  public function render(ResultRow $values) {
    $values->{$this->field_alias} = $values->{$this->field_alias} > 0
      ? TranslationStatusInterface::TRANSLATION_EXISTING
      : NULL;
    return parent::render($values);
  }

  /**
   * {@inheritdoc}
   *
   * Expression wouldn't work without:
   *
   * @see translation_views_query_views_alter()
   */
  public function query() {
    $table_alias = $this->ensureMyTable();
    $this->query->addField(
      NULL,
      "FIND_IN_SET(:langcode, $table_alias.langs) > 0",
      'translation_status',
      [
        'placeholders' => [':langcode' => '***TRANSLATION_VIEWS_TARGET_LANG***'],
      ]
    );
  }

}
