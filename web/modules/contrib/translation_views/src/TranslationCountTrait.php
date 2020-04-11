<?php

namespace Drupal\translation_views;

use Drupal\views\Plugin\views\query\Sql as ViewsSqlQuery;

/**
 * Defines translation count trait.
 *
 * Used to check if table translations already have joined,
 * if not force to join.
 */
trait TranslationCountTrait {

  /**
   * Ensure that translations table is joined.
   *
   * @param \Drupal\views\Plugin\views\query\Sql $query
   *   Views sql query.
   *
   * @return string
   *   The table alias after joining a table.
   */
  protected function joinLanguages(ViewsSqlQuery &$query) {
    if (empty($query->tables[$this->tableAlias])) {
      $query_base_table = $this->relationship ?: $this->view->storage->get('base_table');

      $entity_type = $this->entityTypeManager->getDefinition($this->getEntityType());
      $keys = $entity_type->getKeys();

      $definition = [
        'type' => 'LEFT',
        'left_field' => $keys['id'],
        'field' => $keys['id'],
        'table' => $query_base_table,
        'left_table' => $query_base_table,
        'include_original_language' => !empty($this->options['include_original_language']),
        'langcodes_as_count' => TRUE,
        'entity_id'  => $keys['id'],
      ];

      $tableAlias = $query->ensureTable($query_base_table, $this->relationship);
      $join = $this->joinHandler->createInstance('translation_views_language_join', $definition);

      return $query->addTable($query_base_table, $tableAlias, $join, $this->tableAlias);
    }
    else {
      return $this->tableAlias;
    }
  }

}
