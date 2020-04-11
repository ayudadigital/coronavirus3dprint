<?php

namespace Drupal\translation_views\Plugin\views\field;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\Boolean;
use Drupal\views\ViewExecutable;

/**
 * Provides a field that adds translation status.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_views_source_equals_row")
 */
class TranslationSourceLangcodeEqualsRowLangcodeField extends Boolean {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->field_alias = 'source_lang_eq_row';
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
  public function query() {
    $table_alias = $this->ensureMyTable();
    $base_table = $this->view->storage->get('base_table');

    $this->query->addField(
      NULL,
      "IF(($table_alias.content_translation_source = $base_table.langcode) OR ($table_alias.default_langcode = 1), 1, 0)",
      'source_lang_eq_row'
    );
  }

}
