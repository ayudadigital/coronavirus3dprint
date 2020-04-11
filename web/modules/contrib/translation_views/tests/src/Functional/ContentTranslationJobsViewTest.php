<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests for fields, filters and sorting (Content translation jobs view).
 *
 * @group translation_views
 */
class ContentTranslationJobsViewTest extends BrowserTestBase {

  protected $profile = 'standard';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'translation_views',
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $langcodes = ['de', 'fr'];
    foreach ($langcodes as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }

    $user = $this->drupalCreateUser([
      'administer site configuration',
      'administer nodes',
      'create article content',
      'access content',
      'edit any article content',
      'administer content translation',
      'translate any entity',
      'create content translations',
      'administer languages',
      'administer content types',
    ]);
    $this->drupalLogin($user);

    // Enable translation for article.
    $edit = [
      'entity_types[node]'                                              => 1,
      'settings[node][article][translatable]'                           => 1,
      'settings[node][article][settings][language][language_alterable]' => 1,
    ];
    $this->drupalPostForm('admin/config/regional/content-language', $edit, t('Save configuration'));
    \Drupal::entityTypeManager()->clearCachedDefinitions();

    // Create node1 in german.
    $edit = [
      'title[0][value]'    => 'node1 de',
      'langcode[0][value]' => 'de',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save'));

    // Create node1 translation in french.
    $edit = [
      'title[0][value]'    => 'node1 fr',
    ];
    $this->drupalPostForm('node/1/translations/add/de/fr', $edit, t('Save (this translation)'));

    // Create node2 in english.
    $edit = [
      'title[0][value]'    => 'node2 en',
      'langcode[0][value]' => 'en',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save'));

    // Create node3 in french.
    $edit = [
      'title[0][value]'    => 'node3 fr',
      'langcode[0][value]' => 'fr',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save'));
  }

  /**
   * Tests that filters works as expected.
   */
  public function testNodesFiltering() {
    $this->drupalGet('translate/content', ['query' => ['translation_target_language' => '***LANGUAGE_site_default***']]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('node1 de');
    $this->assertSession()->pageTextContains('node1 fr');
    $this->assertSession()->pageTextNotContains('node2 en');
    $this->assertSession()->pageTextContains('node3 fr');

    $this->drupalGet('translate/content', ['query' => ['translation_target_language' => 'de']]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('node1 de');
    $this->assertSession()->pageTextNotContains('node1 fr');
    $this->assertSession()->pageTextContains('node2 en');
    $this->assertSession()->pageTextContains('node3 fr');

    $this->drupalGet('translate/content', ['query' => ['translation_target_language' => 'fr']]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('node1 de');
    $this->assertSession()->pageTextNotContains('node1 fr');
    $this->assertSession()->pageTextContains('node2 en');
    $this->assertSession()->pageTextNotContains('node3 fr');

    $this->drupalGet('translate/content', [
      'query' => [
        'translation_target_language' => 'de',
        'langcode'                    => 'en',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('node1 de');
    $this->assertSession()->pageTextNotContains('node1 fr');
    $this->assertSession()->pageTextContains('node2 en');
    $this->assertSession()->pageTextNotContains('node3 fr');

    $this->drupalGet('translate/content', [
      'query' => [
        'translation_target_language' => 'fr',
        'langcode'                    => 'en',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('node1 de');
    $this->assertSession()->pageTextNotContains('node1 fr');
    $this->assertSession()->pageTextContains('node2 en');
    $this->assertSession()->pageTextNotContains('node3 fr');

    $this->drupalGet('translate/content', ['query' => ['langcode' => 'fr']]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('node1 de');
    $this->assertSession()->pageTextContains('node1 fr');
    $this->assertSession()->pageTextNotContains('node2 en');
    $this->assertSession()->pageTextContains('node3 fr');

    $this->drupalGet('translate/content', ['query' => ['langcode' => 'de']]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('node1 de');
    $this->assertSession()->pageTextNotContains('node1 fr');
    $this->assertSession()->pageTextNotContains('node2 en');
    $this->assertSession()->pageTextNotContains('node3 fr');

    $this->drupalGet('translate/content', ['query' => ['langcode' => 'en']]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('node1 de');
    $this->assertSession()->pageTextNotContains('node1 fr');
    $this->assertSession()->pageTextNotContains('node2 en');
    $this->assertSession()->pageTextNotContains('node3 fr');

  }

  /**
   * Tests that columns' sorting works as expected.
   */
  public function testNodesSorting() {
    // Check title column asc sorting.
    $this->drupalGet('translate/content', [
      'query' => [
        'order' => 'title',
        'sort'  => 'asc',
      ],
    ]);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'node1 de');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'node1 fr');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'node3 fr');

    // Check title column desc sorting.
    $this->drupalGet('translate/content', [
      'query' => [
        'order' => 'title',
        'sort'  => 'desc',
      ],
    ]);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'node1 de');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'node1 fr');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'node3 fr');

    // Check langcode column asc sorting.
    $this->drupalGet('translate/content', [
      'query' => [
        'order' => 'langcode',
        'sort'  => 'asc',
      ],
    ]);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'German');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'French');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'French');

    // Check langcode column desc sorting.
    $this->drupalGet('translate/content', [
      'query' => [
        'order' => 'langcode',
        'sort'  => 'desc',
      ],
    ]);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'French');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'French');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'German');
  }

}
