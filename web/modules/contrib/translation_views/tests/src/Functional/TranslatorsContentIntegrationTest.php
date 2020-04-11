<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\translators_content\Functional\TranslatorsContentTestsTrait;

/**
 * Class TranslatorsContentIntegrationTest.
 *
 * @package Drupal\Tests\translation_views\Functional
 *
 * @group translation_views
 * @requires module translators
 */
class TranslatorsContentIntegrationTest extends BrowserTestBase {
  use TranslatorsContentTestsTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['translation_views_translators_test'];

  /**
   * Translators skills service.
   *
   * @var \Drupal\translators\Services\TranslatorSkills
   */
  protected $translatorSkills;
  /**
   * User registered skills.
   *
   * @var array
   */
  protected static $registeredSkills = ['en', 'fr'];
  /**
   * User unregistered skills.
   *
   * @var array
   */
  protected static $unregisteredSkills = ['de', 'sq'];
  /**
   * Default language ID.
   *
   * @var string
   */
  protected $defaultLanguage = 'en';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->setUpTest();
  }

  /**
   * Additional steps for tests set up.
   */
  protected function setUpTest() {
    $this->drupalLogin($this->rootUser);
    $this->translatorSkills = $this->container->get('translators.skills');
    $this->createLanguages();
    $this->enableTranslation('node', 'article');
    $this->drupalLogout();
  }

  /**
   * Get array of all testing languages.
   *
   * @return array
   *   All testing langcodes array.
   */
  private static function getAllTestingLanguages() {
    return array_merge(static::$registeredSkills, static::$unregisteredSkills);
  }

  /**
   * Change language settings for entity types.
   *
   * @param string $category
   *   Entity category (e.g. node).
   * @param string $subcategory
   *   Entity subcategory (e.g. article).
   */
  protected function enableTranslation($category, $subcategory) {
    $this->drupalPostForm('admin/config/regional/content-language', [
      "entity_types[$category]"                                                   => 1,
      "settings[$category][$subcategory][translatable]"                           => 1,
      "settings[$category][$subcategory][settings][language][language_alterable]" => 1,
    ], 'Save configuration');
    \Drupal::entityTypeManager()->clearCachedDefinitions();
  }

  /**
   * Register translation skills for testing.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function registerTestSkills() {
    $this->addSkill(static::$registeredSkills);
    foreach (static::$registeredSkills as $skill) {
      $this->assertTrue($this->translatorSkills->hasLangcode($skill));
    }
  }

  /**
   * Create additional languages for testing.
   */
  protected function createLanguages() {
    try {
      foreach (static::getAllTestingLanguages() as $language) {
        if ($language === $this->defaultLanguage) {
          continue;
        }
        $this->assertEquals(1, ConfigurableLanguage::createFromLangcode($language)->save());
      }
    }
    catch (EntityStorageException $e) {
      $this->fail('Additional languages have not been created');
    }
  }

  /**
   * Simply check that all required modules have been installed.
   */
  public function testDependencyInstallation() {
    $this->assertTrue($this->container->get('module_handler')
      ->moduleExists('translators'));
    $this->assertTrue($this->container->get('module_handler')
      ->moduleExists('translators_content'));
    $this->assertTrue($this->container->has('translators.skills'));
  }

  /**
   * Test Content Translators integration for target language filter.
   */
  public function testTranslatorsLanguageFilterInView() {
    $this->drupalLogin($this->rootUser);
    $this->registerTestSkills();
    for ($i = 1; $i <= 10; $i++) {
      Node::create([
        'type' => 'article',
        'title' => 'French node ' . $i,
        'langcode' => 'fr',
      ])
        ->addTranslation('en', ['title' => 'English translation ' . $i])
        ->save();
    }

    $this->drupalGet('/test-translators-content-filter');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->statusCodeNotEquals(404);

    // Find langcode field element.
    $langcode_field = $this->getSession()
      ->getPage()
      ->findField('translation_target_language');
    $this->assertNotNull($langcode_field);

    // Get all existing options of the langcode filter dropdown.
    $options = $langcode_field->findAll('xpath', '//option');
    $this->assertNotNull($options);

    // Prepare array of options' values.
    $language_options = array_map(function ($option) {
      return $option->getAttribute('value') ?: $option->getText();
    }, $options);

    $this->assertCount(4, $language_options);
    $this->assertContains('en', $language_options);
    $this->assertContains('fr', $language_options);
    $this->assertContains('de', $language_options);
    $this->assertContains('sq', $language_options);

    $this->drupalGet('/admin/structure/views/nojs/handler/test_translators_content_integration/page_1/filter/translation_target_language');
    // Check for the default state of the options.
    $this->assertSession()->checkboxNotChecked('options[limit]');
    $this->assertSession()->checkboxNotChecked('options[column][source]');
    $this->assertSession()->checkboxChecked('options[column][target]');
    // Update options.
    $this->drupalPostForm(NULL, [
      'options[limit]'        => 1,
      'options[column][source]' => 1,
      'options[column][target]'   => 1,
    ], 'Apply');
    $this->click('input[value="Save"]');

    $this->drupalGet('/test-translators-content-filter');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->statusCodeNotEquals(404);

    // Find langcode field element.
    $langcode_field = $this->getSession()
      ->getPage()
      ->findField('translation_target_language');
    $this->assertNotNull($langcode_field);

    // Get all existing options of the langcode filter dropdown.
    $options = $langcode_field->findAll('xpath', '//option');
    $this->assertNotNull($options);

    // Prepare array of options' values.
    $language_options = array_map(function ($option) {
      return $option->getAttribute('value') ?: $option->getText();
    }, $options);

    $this->assertCount(2, $language_options);
    $this->assertContains('en', $language_options);
    $this->assertContains('fr', $language_options);
    $this->assertNotContains('de', $language_options);
    $this->assertNotContains('sq', $language_options);

    // No results in views for users without registered translation skills.
    $this->removeSkills();
    $this->drupalGet('/test-translators-content-filter');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('French node');

    // Find langcode field element.
    $langcode_field = $this->getSession()
      ->getPage()
      ->findField('translation_target_language');
    $this->assertNotNull($langcode_field);

    // Get all existing options of the langcode filter dropdown.
    $options = $langcode_field->findAll('xpath', '//option');
    $this->assertNotNull($options);

    // Prepare array of options' values.
    $language_options = array_map(function ($option) {
      return $option->getAttribute('value') ?: $option->getText();
    }, $options);

    $this->assertCount(1, $language_options);
    $this->assertContains('All', $language_options);

  }

  /**
   * Test Content Translators integration for target language filter.
   */
  public function testTranslatorsOperationLinks() {
    $userTranslatorsLimited = $this->createUser([
      'translators_content create content translations',
      'translators_content update content translations',
      'translators_content delete content translations',
      'translate any entity',
    ]);
    $langcodes = static::getAllTestingLanguages();
    Node::create([
      'type' => 'article',
      'title' => "English node",
      'langcode' => 'en',
    ])->save();

    $this->drupalLogin($userTranslatorsLimited);
    $this->registerTestSkills();
    // Check Add translation.
    $this->drupalGet('/test-translators-content-filter', [
      'query' => [
        'langcode' => 'en',
        'translation_target_language' => 'fr',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations ul li a",
        'Add'
      );
    $this->drupalGet('/test-translators-content-filter', [
      'query' => [
        'langcode' => 'en',
        'translation_target_language' => 'de',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextNotContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations",
        'Add'
      );
    // Check edit Edit and Delete translation.
    Node::load(1)->addTranslation('fr', ['title' => 'French translation '])
      ->save();
    $this->drupalGet('/test-translators-content-filter', [
      'query' => [
        'langcode' => 'en',
        'translation_target_language' => 'fr',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations ul .edit a",
        'Edit'
      );
    $this->assertSession()
      ->elementTextContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations ul .delete a",
        'Delete'
      );
    $this->drupalGet('/test-translators-content-filter', [
      'query' => [
        'langcode' => 'en',
        'translation_target_language' => 'de',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextNotContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations",
        'Edit'
      );
    $this->assertSession()
      ->elementTextNotContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations",
        'Delete'
      );
  }

}
