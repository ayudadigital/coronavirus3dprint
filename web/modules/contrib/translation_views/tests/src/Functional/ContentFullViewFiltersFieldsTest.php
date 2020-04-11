<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\views\Tests\ViewTestData;

/**
 * Tests for fields, filters and sorting for content entity.
 *
 * @group translation_views
 */
class ContentFullViewFiltersFieldsTest extends ViewTestBase {

  protected $profile = 'standard';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['translation_views_test_views'];

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['translation_views_all_filters_and_fields'];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp($import_test_views);

    $user = $this->drupalCreateUser([
      'administer site configuration',
      'administer nodes',
      'administer views',
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

    ViewTestData::createTestViews(get_class($this), ['translation_views_test_views']);

    $langcodes = ['de', 'fr'];
    foreach ($langcodes as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }

    // Enable translation for article.
    $edit = [
      'entity_types[node]'                                              => 1,
      'settings[node][article][translatable]'                           => 1,
      'settings[node][article][settings][language][language_alterable]' => 1,
    ];
    $this->drupalPostForm('admin/config/regional/content-language', $edit, t('Save configuration'));
    \Drupal::entityTypeManager()->clearCachedDefinitions();

    // Create a node in en (node1).
    $edit = [
      'title[0][value]'    => '001_en_title_node1',
      'langcode[0][value]' => 'en',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save'));
    // Create a translation in fr (node1).
    $edit = [
      'title[0][value]' => '002_fr_title_node1',
    ];
    $this->drupalPostForm('node/1/translations/add/en/fr', $edit, t('Save (this translation)'));
    // Create a translation in de (node1).
    $edit = [
      'title[0][value]' => '003_de_title_node1',
    ];
    $this->drupalPostForm('node/1/translations/add/en/de', $edit, t('Save (this translation)'));

    // Create a node in de (node2).
    $edit = [
      'title[0][value]'    => '004_de_title_node2',
      'langcode[0][value]' => 'de',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save'));
    // Create a translation in fr (node2).
    $edit = [
      'title[0][value]' => '005_fr_title_node2',
    ];
    $this->drupalPostForm('node/2/translations/add/de/fr', $edit, t('Save (this translation)'));

  }

  /**
   * Tests the filtering.
   */
  public function testFilters() {
    $this->drupalGet('translation-views-all-filters-and-fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->pageTextContains('Translation views all filters and fields');
    $this->assertSession()->pageTextNotContains('001_en_title_node1');
    $this->assertSession()->pageTextContains('002_fr_title_node1');
    $this->assertSession()->pageTextContains('003_de_title_node1');
    $this->assertSession()->pageTextContains('004_de_title_node2');
    $this->assertSession()->pageTextContains('005_fr_title_node2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => '***LANGUAGE_site_default***',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('001_en_title_node1');
    $this->assertSession()->pageTextContains('002_fr_title_node1');
    $this->assertSession()->pageTextContains('003_de_title_node1');
    $this->assertSession()->pageTextNotContains('004_de_title_node2');
    $this->assertSession()->pageTextNotContains('005_fr_title_node2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'de',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('001_en_title_node1');
    $this->assertSession()->pageTextNotContains('002_fr_title_node1');
    $this->assertSession()->pageTextNotContains('003_de_title_node1');
    $this->assertSession()->pageTextNotContains('004_de_title_node2');
    $this->assertSession()->pageTextContains('005_fr_title_node2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'fr',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('001_en_title_node1');
    $this->assertSession()->pageTextNotContains('002_fr_title_node1');
    $this->assertSession()->pageTextNotContains('003_de_title_node1');
    $this->assertSession()->pageTextNotContains('004_de_title_node2');
    $this->assertSession()->pageTextNotContains('005_fr_title_node2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => 'de',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('001_en_title_node1');
    $this->assertSession()->pageTextContains('002_fr_title_node1');
    $this->assertSession()->pageTextNotContains('003_de_title_node1');
    $this->assertSession()->pageTextNotContains('004_de_title_node2');
    $this->assertSession()->pageTextContains('005_fr_title_node2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => 'fr',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('001_en_title_node1');
    $this->assertSession()->pageTextNotContains('002_fr_title_node1');
    $this->assertSession()->pageTextContains('003_de_title_node1');
    $this->assertSession()->pageTextContains('004_de_title_node2');
    $this->assertSession()->pageTextNotContains('005_fr_title_node2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => '1',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('001_en_title_node1');
    $this->assertSession()->pageTextContains('002_fr_title_node1');
    $this->assertSession()->pageTextContains('003_de_title_node1');
    $this->assertSession()->pageTextNotContains('004_de_title_node2');
    $this->assertSession()->pageTextNotContains('005_fr_title_node2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => '0',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('001_en_title_node1');
    $this->assertSession()->pageTextNotContains('002_fr_title_node1');
    $this->assertSession()->pageTextNotContains('003_de_title_node1');
    $this->assertSession()->pageTextContains('004_de_title_node2');
    $this->assertSession()->pageTextContains('005_fr_title_node2');
  }

  /**
   * Test the sorting.
   */
  public function testSorting() {
    // Title.
    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'title',
        'sort'                        => 'asc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', '002_fr_title_node1');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', '003_de_title_node1');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', '004_de_title_node2');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', '005_fr_title_node2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'title',
        'sort'                        => 'desc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', '002_fr_title_node1');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', '003_de_title_node1');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', '004_de_title_node2');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', '005_fr_title_node2');

    // Translation language.
    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'langcode',
        'sort'                        => 'asc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'German');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'German');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'French');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', 'French');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'langcode',
        'sort'                        => 'desc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', 'German');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'German');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'French');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'French');

    // Target language equals default language.
    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'translation_default',
        'sort'                        => 'asc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'No');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'No');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'Yes');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', 'Yes');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'translation_default',
        'sort'                        => 'desc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', 'No');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'No');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'Yes');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'Yes');

    // Translation changed time.
    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'translation_changed',
        'sort'                        => 'asc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextNotContains('css', 'table > tbody > tr:nth-child(1)', ':');
    $this->assertSession()
      ->elementTextNotContains('css', 'table > tbody > tr:nth-child(2)', ':');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', ':');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', ':');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'translation_changed',
        'sort'                        => 'desc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextNotContains('css', 'table > tbody > tr:nth-child(4)', ':');
    $this->assertSession()
      ->elementTextNotContains('css', 'table > tbody > tr:nth-child(3)', ':');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', ':');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', ':');

    // Translation counter.
    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'translation_count',
        'sort'                        => 'asc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', '1');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', '1');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', '2');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', '2');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'translation_count',
        'sort'                        => 'desc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', '1');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', '1');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', '2');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', '2');

    // Translation counter.
    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'translation_status',
        'sort'                        => 'asc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'Not translated');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'Not translated');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'Translated');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', 'Translated');

    $this->drupalGet('translation-views-all-filters-and-fields', [
      'query' => [
        'content_translation_source'  => 'All',
        'translation_target_language' => '***LANGUAGE_site_default***',
        'translation_default'         => 'All',
        'translation_status'          => 'All',
        'order'                       => 'translation_status',
        'sort'                        => 'desc',
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(4)', 'Not translated');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(3)', 'Not translated');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(2)', 'Translated');
    $this->assertSession()
      ->elementTextContains('css', 'table > tbody > tr:nth-child(1)', 'Translated');
  }

}
