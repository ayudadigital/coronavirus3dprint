<?php

namespace Drupal\Tests\extra_field\Functional;

/**
 * Tests the extra field Display on entity UI pages.
 *
 * @group extra_field
 */
class ExtraFieldDisplayUITest extends ExtraFieldBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'extra_field',
    'node',
    'field_ui',
  ];

  /**
   * Entity display for each content type.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface[]
   */
  protected $entityDisplay;

  /**
   * The URL to the manage display interface.
   *
   * @var string[]
   */
  protected $manageDisplayUrl;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();
    $adminUser = $this->drupalCreateUser(['administer node display']);
    $this->drupalLogin($adminUser);
    $this->entityDisplay['first_node_type'] = $this->setupContentEntityDisplay('first_node_type');
    $this->manageDisplayUrl['first_node_type'] = 'admin/structure/types/manage/first_node_type/display/teaser';
    $this->entityDisplay['another_node_type'] = $this->setupContentEntityDisplay('another_node_type');
    $this->manageDisplayUrl['another_node_type'] = 'admin/structure/types/manage/another_node_type/display/teaser';

    $this->setupEnableExtraFieldTestModule();
  }

  /**
   * Test if OneNodeType plugin is displayed on entity display pages.
   */
  public function testOneNodeTypePlugin() {

    // Check presence 'first_node_type' display page.
    $this->drupalGet($this->manageDisplayUrl['first_node_type']);
    $this->assertSession()->pageTextContains('Extra field for first node type');

    // Check enabled/disabled and weight.
    $this->assertSession()->fieldValueEquals('fields[extra_field_one_node_type_test][region]', 'hidden');
    $this->assertSession()->fieldValueEquals('fields[extra_field_one_node_type_test][weight]', '0');

    // Check presence 'another_node_type' display page.
    $this->drupalGet($this->manageDisplayUrl['another_node_type']);
    $this->assertSession()->pageTextNotContains('Extra field for first node type');
  }

  /**
   * Test if AllNodeTypes plugin is displayed on entity display pages.
   */
  public function testAllNodeTypesPlugin() {

    // Check presence 'first_node_type' display page.
    $this->drupalGet($this->manageDisplayUrl['first_node_type']);
    $this->assertSession()->pageTextContains('Extra field for all node types');

    // Check enabled/disabled and weight.
    $this->assertSession()->fieldValueEquals('fields[extra_field_all_node_types_test][region]', 'content');
    $this->assertSession()->fieldValueEquals('fields[extra_field_all_node_types_test][weight]', '7');

    // Check presence 'another_node_type' display page.
    $this->drupalGet($this->manageDisplayUrl['another_node_type']);
    $this->assertSession()->pageTextContains('Extra field for all node types');
  }

}
