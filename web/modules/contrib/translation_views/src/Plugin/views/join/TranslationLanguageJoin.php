<?php

namespace Drupal\translation_views\Plugin\views\join;

use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\join\JoinPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Special join to show all translatable langcodes per one row.
 *
 * @ViewsJoin("translation_views_language_join")
 */
class TranslationLanguageJoin extends JoinPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  protected $eid;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->eid = $this->configuration['entity_id'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildJoin($select_query, $table, $view_query) {
    $query = $this->database->select($this->table, 'efd');
    $query->fields('efd', [$this->eid]);

    if (!empty($this->configuration['langcodes_as_count'])) {
      $query->addExpression("COUNT(efd.langcode)", 'count_langs');
      if (isset($this->configuration['include_original_language'])
          && $this->configuration['include_original_language'] == FALSE) {
        $query->where('efd.default_langcode != 1');
      }
    }
    else {
      $query->addExpression("GROUP_CONCAT(efd.langcode separator ',')", 'langs');
    }
    $query->groupBy('efd.' . $this->eid);
    $this->configuration['table formula'] = $query;

    parent::buildJoin($select_query, $table, $view_query);
  }

}
