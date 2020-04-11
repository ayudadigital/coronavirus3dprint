<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\views\Tests\ViewTestData;

/**
 * Class TranslationOperationTargetLanguageTest.
 *
 * @package Drupal\Tests\translation_views\Functional
 *
 * @group translation_views
 */
class TranslationOperationTargetLanguageTest extends ViewTestBase {

  /**
   * List of the additional language IDs to be created for the tests.
   *
   * @var array
   */
  private static $langcodes = ['fr', 'de', 'it', 'af', 'sq'];
  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'content_translation',
    'node',
    'translation_views',
    'translation_views_test_views',
  ];
  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';
  /**
   * Testing views ID array.
   *
   * @var array
   */
  public static $testViews = ['translation_views_all_filters_and_fields'];
  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  private $adminUser;
  /**
   * Default language ID.
   *
   * @var string
   */
  private $defaultLangcode;

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp($import_test_views);

    $this->adminUser = $this->createUser([], 'test_admin', TRUE);
    $this->drupalLogin($this->adminUser);

    $this->defaultLangcode = \Drupal::languageManager()
      ->getDefaultLanguage()
      ->getId();

    // Set up testing views.
    ViewTestData::createTestViews(get_class($this), ['translation_views_test_views']);
    try {
      $this->setUpLanguages();
    }
    catch (EntityStorageException $e) {
      $this->verbose($e->getMessage());
    }
    // Enable translation for Article nodes.
    $this->enableTranslation('node', 'article');

    // Create testing node.
    $this->drupalPostForm('node/add/article', [
      'title[0][value]' => $this->randomString(),
    ], 'Save');

    $this->drupalLogout();
  }

  /**
   * Set up languages.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function setUpLanguages() {
    foreach (self::$langcodes as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
  }

  /**
   * Change language settings for entity types.
   *
   * @param string $category
   *   Entity category (e.g. node).
   * @param string $subcategory
   *   Entity subcategory (e.g. article).
   */
  private function enableTranslation($category, $subcategory) {
    $this->drupalPostForm('admin/config/regional/content-language', [
      "entity_types[$category]"                                                   => 1,
      "settings[$category][$subcategory][translatable]"                           => 1,
      "settings[$category][$subcategory][settings][language][language_alterable]" => 1,
    ], 'Save configuration');
    \Drupal::entityTypeManager()->clearCachedDefinitions();
  }

  /**
   * Translate node all specified languages.
   */
  private function translateNode() {
    $node = Node::load(1);
    foreach (self::$langcodes as $langcode) {
      if (!$node->hasTranslation($langcode)) {
        $this->assertFalse($node->hasTranslation($langcode));
        $node->addTranslation($langcode, ['title' => $this->randomMachineName()])->save();
        $this->assertTrue($node->hasTranslation($langcode));
      }
    }
  }

  /**
   * Generate random target language ID from available list.
   *
   * @return string
   *   Language ID.
   */
  private function generateTargetLanguage() {
    $target_language = self::$langcodes[mt_rand(0, 4)];
    $this->assertNotNull($target_language);
    return $target_language;
  }

  /**
   * Assert that the response HTTP code is 200.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  private function assertResponseOk() {
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test translation operations target language.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testTranslationOperationsTargetLanguage() {
    $base_selector = 'table > tbody > tr:nth-child(1) .views-field-translation-operations ul li';
    $this->drupalLogin($this->adminUser);

    // Check for "Add" links target languages.
    $target_language = $this->generateTargetLanguage();
    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => $target_language,
        'translation_default'         => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertResponseOk();
    $expected_create_link = "/$target_language/node/1/translations/add/{$this->defaultLangcode}/$target_language";
    $this->assertSession()
      ->elementAttributeContains(
        'css',
        "{$base_selector} a",
        'href',
        $expected_create_link
      );
    // Translate nodes.
    $this->translateNode();
    // Check for "Edit" and "Delete" links target languages.
    $target_language = $this->generateTargetLanguage();
    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => $target_language,
        'translation_default'         => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertResponseOk();
    $this->assertSession()
      ->elementAttributeContains(
        'css',
        "{$base_selector}.edit a",
        'href',
        "/$target_language/node/1/edit"
      );
    $this->assertSession()
      ->elementAttributeContains(
        'css',
        "{$base_selector}.delete a",
        'href',
        "/$target_language/node/1/delete"
      );
  }

}
