<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\views\Tests\ViewTestData;
use Drupal\workflows\Entity\Workflow;

/**
 * Class ContentModerationIntegration.
 *
 * @package Drupal\Tests\translation_views\Functional
 *
 * @group translation_views
 */
class ContentModerationIntegration extends ViewTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';
  /**
   * {@inheritdoc}
   */
  public static $modules = ['translation_views_test_views'];
  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['content_moderation_integration_test'];

  /**
   * {@inheritdoc}
   */
  public function setUp($import_test_views = TRUE) {
    // Inherit set up from the parent class.
    parent::setUp($import_test_views);
    // Login as a root user.
    $this->drupalLogin($this->rootUser);
    // Import test views.
    ViewTestData::createTestViews(get_class($this), ['translation_views_test_views']);
    // Create additional language.
    ConfigurableLanguage::createFromLangcode('af')->save();

    // Enable translation for article nodes.
    $this->drupalPostForm('admin/config/regional/content-language', [
      "entity_types[node]"                                              => 1,
      "settings[node][article][translatable]"                           => 1,
      "settings[node][article][settings][language][language_alterable]" => 1,
    ], 'Save configuration');
    // Flush definitions caches.
    \Drupal::entityTypeManager()->clearCachedDefinitions();

    // Enable moderation state for article nodes.
    $workflow = Workflow::load('editorial');
    $workflow->getTypePlugin()->addEntityTypeAndBundle('node', 'article');
    $workflow->save();
    // Logout.
    $this->drupalLogout();
  }

  /**
   * Test view's field "Translation Moderation State".
   */
  public function testTranslationModerationFieldViews() {
    // Login as a root user.
    $this->drupalLogin($this->rootUser);
    // Create node for test.
    $node = $this->createNode(['type' => 'article']);

    // Ensure we have moderation state "Draft" by default
    // in the newly created node.
    $this->assertTrue($node->hasField('moderation_state'));
    $this->assertFalse($node->get('moderation_state')->isEmpty());
    $this->assertEquals('draft', $node->get('moderation_state')->first()->getString());

    // Go to the testing view's page.
    $this->drupalGet('/content-moderation-integration-test');
    $this->assertSession()->statusCodeEquals(200);
    // Check that created node has "Draft" moderation state by default.
    $this->assertSession()->elementTextContains(
      'css',
      '.views-field-translation-moderation-state span',
      'Draft'
    );

    // Change moderation state to "Published".
    $node->set('moderation_state', 'published')->save();

    // Reload the testing view's page.
    $this->drupalGet('/content-moderation-integration-test');
    $this->assertSession()->statusCodeEquals(200);
    // Check that the moderation state value has been changed in view's field.
    $this->assertSession()->elementTextContains(
      'css',
      '.views-field-translation-moderation-state span',
      'Published'
    );
  }

}
