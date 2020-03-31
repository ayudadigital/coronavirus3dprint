<?php

namespace Drupal\node\Plugin\views\argument;

use Drupal\node\NodeStorageInterface;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;

/**
 * Argument handler to accept a node id.
 *
 * @ViewsArgument("node_nid")
 */
class Nid extends NumericArgument {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs the Nid object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\node\NodeStorageInterface $node_storage
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, NodeStorageInterface $node_storage, EntityRepositoryInterface $entity_repository = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $node_storage;
    if (!$entity_repository) {
      @trigger_error('Calling \Drupal\node\Plugin\views\argument\Nid::__construct() without the $entity_repository argument is deprecated in drupal:8.9.0 and is required in drupal:10.0.0. See https://www.drupal.org/node/3102631', E_USER_DEPRECATED);
      $entity_repository = \Drupal::service('entity.repository');
    }
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('entity.repository')
    );
  }

  /**
   * Override the behavior of title(). Get the title of the node.
   */
  public function titleQuery() {
    $titles = [];

    $nodes = $this->nodeStorage->loadMultiple($this->value);
    foreach ($nodes as $node) {
      $titles[] = $this->entityRepository->getTranslationFromContext($node)->label();
    }
    return $titles;
  }

}
