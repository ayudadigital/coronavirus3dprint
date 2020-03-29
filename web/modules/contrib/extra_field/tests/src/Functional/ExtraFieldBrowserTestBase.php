<?php

namespace Drupal\Tests\extra_field\Functional;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Tests\BrowserTestBase;

/**
 * Base class for Extra Field browser tests.
 */
abstract class ExtraFieldBrowserTestBase extends BrowserTestBase {

  /**
   * The machine name of the node type used for the test.
   *
   * @var string
   */
  protected $contentTypeName;

  /**
   * Setup the node type with view display.
   *
   * @param string $contentType
   *   Name of node bundle to be generated.
   */
  public function setupContentEntityDisplay($contentType) {

    $this->contentTypeName = $contentType;
    $this->createContentType(['type' => $contentType]);

    EntityViewDisplay::create(array(
      'targetEntityType' => 'node',
      'bundle' => $contentType,
      'mode' => 'default',
      'status' => TRUE,
    ));
  }

  /**
   * Creates a node.
   *
   * @param string $contentType
   *   Content type of the node.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The new node.
   */
  public function createContent($contentType) {

    $this->createContentType(['type' => $contentType]);
    /** @var \Drupal\Core\Entity\ContentEntityInterface $node */
    $node = \Drupal::entityTypeManager()->getStorage('node')->create([
      'type' => $contentType,
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    return $node;
  }

  /**
   * Enables the extra field test module.
   *
   * The extra_field_test module must be enabled _after_ entity types are
   * created. Enabling it earlier will lead to a situation where
   * ExtraFieldDisplayManager::allEntityBundles caches only the first node
   * type that was created. It is expected that this situation will not occur
   * outside of tests.
   */
  public function setupEnableExtraFieldTestModule() {

    $modules = ['extra_field_test'];
    $success = $this->container->get('module_installer')->install($modules, TRUE);
    $this->assertTrue($success, new FormattableMarkup('Enabled modules: %modules', ['%modules' => implode(', ', $modules)]));
    $this->resetAll();
  }

}
