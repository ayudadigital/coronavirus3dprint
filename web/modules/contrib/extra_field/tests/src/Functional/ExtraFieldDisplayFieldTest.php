<?php

namespace Drupal\Tests\extra_field\Functional;

/**
 * Tests the extra_field Display with field wrapper.
 *
 * @group extra_field
 */
class ExtraFieldDisplayFieldTest extends ExtraFieldBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'extra_field',
    'node',
  ];

  /**
   * A node that contains the extra fields.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $firstNode;

  /**
   * A second node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $secondNode;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->firstNode = $this->createContent('first_node_type');
    $this->secondNode = $this->createContent('another_node_type');
    $this->setupEnableExtraFieldTestModule();
  }

  /**
   * Test the output of field with single value item.
   */
  public function testFirstNodeTypeFields() {

    $url = $this->firstNode->toUrl();
    $this->drupalGet($url);

    // Test the output of field with single value item.
    $this->assertSession()->responseContains('<div class="field__item">Output from SingleTextFieldTest</div>');
    $this->assertSession()->responseContains('<div class="field__label">Single text</div>');
    $this->assertSession()->responseContains('field--name-field-single-text');
    $this->assertSession()->responseContains('field--type-single-text');
    $this->assertSession()->responseContains('field--label-inline');

    // Test the output of field with multiple value items.
    $this->assertSession()->responseContains('<div class="field__item">Aap</div>');
    $this->assertSession()->responseContains('<div class="field__item">Noot</div>');
    $this->assertSession()->responseContains('field--name-extra-field-multiple-text-test');

    // Test the output of field with cacheable dependency.
    $this->assertSession()->responseContains('<div class="field__label">Related pages</div>');
    $this->assertSession()->responseContains('<div class="field__item">' . $this->secondNode->label() . '</div>');
    $this->assertCacheTag('node:' . $this->secondNode->id());

    // Test the output of field without content.
    $this->assertSession()->responseNotContains('field--name-extra-field-empty-formatted-test');
  }

}
