<?php

namespace Drupal\Tests\fixed_text_link_formatter\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\link\LinkItemInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the correct function of the Fixed Text Link formatter.
 *
 * @group fixed_text_link_formatter
 */
class FixedTextLinkTest extends BrowserTestBase {

  /**
   * {@inheritDoc}
   */
  public static $modules = ['node', 'fixed_text_link_formatter'];

  /**
   * @var \Drupal\node\Entity\NodeType
   */
  private $nodeType;

  /**
   * @var \Drupal\field\FieldStorageConfigInterface
   */
  private $fieldStorage;

  /**
   * @var \Drupal\Core\Field\FieldConfigInterface
   */
  private $field;

  private $linkFieldName;

  /**
   * {@inheritDoc}
   */
  public function setUp() {
    parent::setUp();

    $this->nodeType = $this->drupalCreateContentType();

    $this->linkFieldName = mb_strtolower($this->randomMachineName());
    // Create a field with settings to validate.
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => $this->linkFieldName,
      'entity_type' => 'node',
      'type' => 'link',
    ]);
    $this->fieldStorage->save();
    $this->field = FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'bundle' => $this->nodeType->id(),
      'settings' => [
        'title' => DRUPAL_DISABLED,
        'link_type' => LinkItemInterface::LINK_GENERIC,
      ],
    ]);
    $this->field->save();
    entity_get_form_display('node', $this->nodeType->id(), 'default')
      ->setComponent($this->linkFieldName, [
        'type' => 'link_default',
        'settings' => [
          'placeholder_url' => 'http://example.com',
        ],
      ])
      ->save();
  }

  /**
   * Tests that the link is formatted with the configured options.
   */
  public function testFormatsWithConfiguredText() {
    $linkText = 'View our amazing website';
    $linkClass = 'test-class';
    entity_get_display('node', $this->nodeType->id(), 'full')
      ->setComponent($this->linkFieldName, [
        'type' => 'fixed_text_link',
        'settings' => [
          'link_text' => $linkText,
          'link_class' => $linkClass,
        ],
      ])
      ->save();

    $node = $this->createNode([
      'type' => $this->nodeType->id(),
      $this->linkFieldName => ['uri' => 'http://example.com/test'],
    ]);
    $this->getSession()->visit($node->toUrl()->toString());

    $this->assertSession()->linkExists($linkText);
    $this->assertSession()->elementExists('css', ".$linkClass");
  }

  /**
   * Tests that the title override works correctly.
   */
  public function testRegression3054339() {
    $linkText = 'View amazing website';
    entity_get_display('node', $this->nodeType->id(), 'full')
      ->setComponent($this->linkFieldName, [
        'type' => 'fixed_text_link',
        'settings' => [
          'link_text' => $linkText,
          'allow_override' => TRUE,
        ],
      ])
      ->save();

    $overrideText = 'Override test';
    $node = $this->createNode([
      'type' => $this->nodeType->id(),
      $this->linkFieldName => [
        'uri' => 'http://example.com/test',
        'title' => $overrideText,
      ],
    ]);
    $this->getSession()->visit($node->toUrl()->toString());

    $this->assertSession()->linkExists($overrideText);
    $this->assertSession()->pageTextNotContains($linkText);
  }

}
