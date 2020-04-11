<?php

namespace Drupal\translation_views\Plugin\views\filter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\translation_views\TranslationCountTrait;
use Drupal\views\Plugin\views\filter\NumericFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Plugin\ViewsHandlerManager;

/**
 * Translation count filter.
 *
 * Filter rows by number of exisitng translations for a given source entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsFilter("translation_views_translation_count")
 */
class TranslationCountFilter extends NumericFilter implements ContainerFactoryPluginInterface {
  use TranslationCountTrait;

  /**
   * Translation table alias.
   *
   * @var string
   */
  public $tableAlias = 'translations';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.views.join')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ViewsHandlerManager $join_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->joinHandler = $join_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $op    = $this->operator;

    $join_alias = $this->joinLanguages($query);

    $query->addWhereExpression(
      $this->options['group'],
      "IF(ISNULL($join_alias.count_langs), 0, $join_alias.count_langs) $op :value",
      [':value' => $this->value['value']]
    );
  }

}
