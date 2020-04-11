<?php

namespace Drupal\translation_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * Filter rows by equity of two langcodes.
 *
 * The source and target langcodes is used for comparision.
 *
 * @ViewsFilter("translation_views_source_equals_row")
 */
class TranslationSourceLangcodeEqualsRowLangcodeFilter extends BooleanOperator {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $table_alias = $this->ensureMyTable();
    $base_table = $this->view->storage->get('base_table');

    $this->query->addWhereExpression(
      $this->options['group'],
      "IF(($table_alias.content_translation_source $this->operator $base_table.langcode) OR ($table_alias.default_langcode $this->operator 1), 1, 0) = :value", [
        ':value' => $this->value,
      ]
    );
  }

}
