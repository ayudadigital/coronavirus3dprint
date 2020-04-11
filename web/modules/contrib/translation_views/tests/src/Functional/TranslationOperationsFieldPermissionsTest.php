<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\user\Entity\Role;
use Drupal\views\Tests\ViewTestData;

/**
 * Class TranslationOperationsFieldPermissionsTest.
 *
 * @group translation_views
 *
 * @package Drupal\Tests\translation_views\Functional
 */
class TranslationOperationsFieldPermissionsTest extends ViewTestBase {

  /**
   * List of the additional language IDs to be created for the tests.
   *
   * @var array
   */
  private static $langcodes = ['fr', 'de', 'it', 'af', 'sq'];
  /**
   * User with permission to create translation.
   *
   * @var \Drupal\user\Entity\User
   */
  private $userCreate;
  /**
   * User with permission to update translation.
   *
   * @var \Drupal\user\Entity\User
   */
  private $userUpdate;
  /**
   * User with permission to delete translation.
   *
   * @var \Drupal\user\Entity\User
   */
  private $userDelete;
  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  private $adminUser;
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
  public static $testViews = ['test_operations_links'];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp($import_test_views);

    $this->setUpUsers();
    $this->drupalLogin($this->adminUser);

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
   * Set up users with different sets of permissions.
   */
  private function setUpUsers() {
    $this->adminUser  = $this->createUser([], 'test_admin', TRUE);
    $this->userCreate = $this->createUser(['create content translations']);
    $this->userUpdate = $this->createUser(['update content translations']);
    $this->userDelete = $this->createUser(['delete content translations']);
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
   * Go to the /translate/content page.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  private function goToTestingView() {
    $target_code = static::$langcodes[mt_rand(0, 4)];
    $this->drupalGet('/test_operations_links', [
      'query' => [
        'translation_target_language' => $target_code,
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Translate node all specified languages.
   */
  private function translateNode() {
    $node = Node::load(1);
    foreach (self::$langcodes as $langcode) {
      if (!$node->hasTranslation($langcode)) {
        $node->addTranslation($langcode, ['title' => $this->randomMachineName()])
          ->save();
      }
    }
  }

  /**
   * Add specific set of permissions to the "authenticated" role.
   *
   * @param array $permissions
   *   Permissions array.
   */
  private function addPermissionsForAuthUser(array $permissions = []) {
    if (!empty($permissions)) {
      $role = Role::load(Role::AUTHENTICATED_ID);
      if ($role instanceof Role) {
        $this->grantPermissions($role, $permissions);
      }
    }
  }

  /**
   * Test translation operation create permissions.
   *
   * @throws \Behat\Mink\Exception\ElementTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testTranslationOperationsCreatePermissions() {
    $default_language = \Drupal::languageManager()->getDefaultLanguage();
    $target_language  = static::$langcodes[mt_rand(0, 4)];
    $this->assertNotNull($target_language);
    $this->assertNotNull($default_language);

    $this->drupalLogin($this->userCreate);

    $this->assertTrue($this->userCreate->hasPermission('create content translations'));
    $this->assertFalse($this->userCreate->hasPermission('translate any entity'));

    $this->drupalGet('/translate/content', [
      'query' => [
        'langcode'                    => $default_language->getId(),
        'translation_target_language' => $target_language,
        'translation_outdated'        => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementNotExists(
      'css',
      'table > tbody > tr:nth-child(1) .views-field-translation-operations ul li a'
    );

    $this->addPermissionsForAuthUser(['translate any entity']);
    $this->assertTrue($this->userCreate->hasPermission('translate any entity'));

    $this->drupalGet('/translate/content', [
      'query' => [
        'langcode'                    => $default_language->getId(),
        'translation_target_language' => $target_language,
        'translation_outdated'        => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains(
        'css',
        "table > tbody > tr:nth-child(1) .views-field-translation-operations ul li a",
        'Add'
      );
  }

  /**
   * Test translation operation update permissions.
   *
   * @throws \Behat\Mink\Exception\ElementTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testTranslationOperationsUpdatePermissions() {
    $this->translateNode();
    $this->assertTrue($this->userUpdate->hasPermission('update content translations'));
    $this->assertFalse($this->userUpdate->hasPermission('translate any entity'));
    $this->drupalLogin($this->userUpdate);
    $this->goToTestingView();
    $this->assertSession()
      ->elementTextNotContains(
        'css',
        ".view-content > div:nth-child(1) .views-field-translation-operations",
        'Edit'
      );
    $this->addPermissionsForAuthUser(['translate article node']);
    $this->assertTrue($this->userUpdate->hasPermission('translate article node'));
    $this->goToTestingView();
    $this->assertSession()
      ->elementTextContains(
        'css',
        ".view-content > div:nth-child(1) .views-field-translation-operations ul li a",
        'Edit'
      );
  }

  /**
   * Test translation operation delete permissions.
   *
   * @throws \Behat\Mink\Exception\ElementTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testTranslationOperationsDeletePermissions() {
    $this->translateNode();
    $this->assertTrue($this->userDelete->hasPermission('delete content translations'));
    $this->assertFalse($this->userDelete->hasPermission('translate any entity'));
    $this->drupalLogin($this->userDelete);
    $this->goToTestingView();
    $this->assertSession()
      ->elementTextNotContains(
        'css',
        ".view-content > div:nth-child(1) .views-field-translation-operations",
        'Delete'
      );
    $this->addPermissionsForAuthUser(['translate article node']);
    $this->assertTrue($this->userDelete->hasPermission('translate article node'));
    $this->goToTestingView();
    $this->assertSession()
      ->elementTextContains(
        'css',
        ".view-content > div:nth-child(1) .views-field-translation-operations ul li a",
        'Delete'
      );
  }

}
