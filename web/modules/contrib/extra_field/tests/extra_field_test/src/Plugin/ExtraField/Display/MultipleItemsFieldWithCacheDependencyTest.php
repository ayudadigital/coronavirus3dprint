<?php

namespace Drupal\extra_field_test\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field Display for a field with multiple items output.
 *
 * @ExtraFieldDisplay(
 *   id = "multiple_text_with_cache_dependency_test",
 *   label = @Translation("Extra field with multiple text item and a cache dependency"),
 *   bundles = {
 *     "node.first_node_type",
 *   },
 *   visible = true
 * )
 */
class MultipleItemsFieldWithCacheDependencyTest extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The render service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a MultipleItemsFieldWithCacheDependencyTest object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The render service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $build = [];

    $another_node_type_nodes = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'another_node_type']);
    foreach ($another_node_type_nodes as $another_node) {
      $build[] = ['#markup' => $another_node->label()];
      $this->renderer->addCacheableDependency($build, $another_node);
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Related pages');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'inline';
  }

}
