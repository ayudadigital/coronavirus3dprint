<?php

namespace Drupal\extra_field\Plugin;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Manages Extra Field plugins.
 *
 * @package Drupal\extra_field\Plugin
 */
class ExtraFieldDisplayManager extends DefaultPluginManager implements ExtraFieldDisplayManagerInterface {

  /**
   * Caches bundles per entity type.
   *
   * @var array
   */
  protected $entityBundles;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor for ExtraFieldDisplayManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager) {

    parent::__construct('Plugin/ExtraField/Display', $namespaces, $module_handler, 'Drupal\extra_field\Plugin\ExtraFieldDisplayInterface', 'Drupal\extra_field\Annotation\ExtraFieldDisplay');

    $this->alterInfo('extra_field_display_info');
    $this->setCacheBackend($cache_backend, 'extra_field_display_plugins');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldInfo() {

    $info = [];
    $definitions = $this->getDefinitions();

    foreach ($definitions as $key => $definition) {
      $entityBundles = $this->supportedEntityBundles($definition['bundles']);

      foreach ($entityBundles as $entityBundle) {
        $entityType = $entityBundle['entity'];
        $bundle = $entityBundle['bundle'];
        $fieldName = $this->fieldName($key);
        $info[$entityType][$bundle]['display'][$fieldName] = [
          'label' => $definition['label'],
          'weight' => $definition['weight'],
          'visible' => $definition['visible'],
        ];
      }
    }

    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public function entityView(array &$build, ContentEntityInterface $entity, EntityViewDisplayInterface $display, $viewMode) {

    $definitions = $this->getDefinitions();
    $entityBundleKey = $this->entityBundleKey($entity->getEntityTypeId(), $entity->bundle());
    foreach ($definitions as $pluginId => $definition) {
      if ($this->matchEntityBundleKey($definition['bundles'], $entityBundleKey)) {

        $factory = $this->getFactory();
        if ($display->getComponent($this->fieldName($pluginId))) {

          /** @var ExtraFieldDisplayInterface $plugin */
          $plugin = $factory->createInstance($pluginId);
          $fieldName = $this->fieldName($pluginId);
          $plugin->setEntity($entity);
          $plugin->setEntityViewDisplay($display);
          $plugin->setViewMode($viewMode);
          $elements = $plugin->view($entity);
          if (!empty($elements)) {
            $build[$fieldName] = $elements;
          }
        }
      }
    }
  }

  /**
   * Checks if the plugin bundle definition matches the entity bundle key.
   *
   * @param string[] $pluginBundles
   *   Defines which entity-bundle pair the plugin can be used for.
   *   Format: [entity type].[bundle] or [entity type].* .
   * @param string $entityBundleKey
   *   The entity-bundle string of a content entity.
   *   Format: [entity type].[bundle] .
   *
   * @return bool
   *   True of the plugin bundle definition matches the entity bundle key.
   */
  protected function matchEntityBundleKey(array $pluginBundles, $entityBundleKey) {

    $match = FALSE;
    foreach ($pluginBundles as $pluginBundle) {
      if (strpos($pluginBundle, '.*')) {
        $match = explode('.', $pluginBundle)[0] == explode('.', $entityBundleKey)[0];
      }
      else {
        $match = $pluginBundle == $entityBundleKey;
      }

      if ($match) {
        break;
      }
    }

    return $match;
  }

  /**
   * Returns entity-bundle combinations this plugin supports.
   *
   * If a wildcard bundle is set, all bundles of the entity will be included.
   *
   * @param string[] $entityBundleKeys
   *   Array of entity-bundle strings that define the bundles for which the
   *   plugin can be used. Format: [entity].[bundle]
   *   '*' can be used as bundle wildcard.
   *
   * @return array
   *   Array of entity and bundle names. Keyed by the [entity].[bundle] key.
   */
  protected function supportedEntityBundles(array $entityBundleKeys) {

    $result = [];
    foreach ($entityBundleKeys as $entityBundleKey) {
      if (strpos($entityBundleKey, '.')) {
        list($entityType, $bundle) = explode('.', $entityBundleKey);
        if ($bundle == '*') {
          foreach ($this->allEntityBundles($entityType) as $bundle) {
            $key = $this->entityBundleKey($entityType, $bundle);
            $result[$key] = [
              'entity' => $entityType,
              'bundle' => $bundle,
            ];
          }
        }
        else {
          $result[$entityBundleKey] = [
            'entity' => $entityType,
            'bundle' => $bundle,
          ];
        }
      }
    }

    return $result;
  }

  /**
   * Returns the bundles that are defined for an entity type.
   *
   * @param string $entityType
   *   The entity type to get the bundles for.
   *
   * @return string[]
   *   Array of bundle names.
   */
  protected function allEntityBundles($entityType) {

    if (!isset($this->entityBundles[$entityType])) {
      $bundleType = $this->entityTypeManager
        ->getDefinition($entityType)
        ->getBundleEntityType();

      if ($bundleType) {
        $bundles = $this->entityTypeManager
          ->getStorage($bundleType)
          ->getQuery()
          ->execute();
      }
      else {
        $bundles = [$entityType => $entityType];
      }
      $this->entityBundles[$entityType] = $bundles;
    }

    return $this->entityBundles[$entityType];
  }

  /**
   * Build the field name string.
   *
   * @param string $pluginId
   *   The machine name of the Extra Field plugin.
   *
   * @return string
   *   Field name.
   */
  protected function fieldName($pluginId) {

    return 'extra_field_' . $pluginId;
  }

  /**
   * Creates a key string with entity type and bundle.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   *
   * @return string
   *   Formatted string.
   */
  protected function entityBundleKey($entityType, $bundle) {

    return "$entityType.$bundle";
  }

}
