<?php

namespace Drupal\Tests\translation_views\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\views\Tests\ViewTestData;

/**
 * Tests for fields, filters and sorting for comment entity.
 *
 * @group translation_views
 */
class CommentFullViewFiltersFieldsTest extends ViewTestBase {

  protected $profile = 'standard';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['translation_views_test_views'];

  public $loremIpsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum eget mi mi.';

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['comment_translation'];

  private $viewPath = 'comment-translation';

  /**
   * Checks that text is in specific row.
   *
   * @param int $row_number
   *   Table row order number.
   * @param string $css_class
   *   Part of the css class of required field.
   * @param string $text
   *   Text that should be found in the element.
   *
   * @throws \Behat\Mink\Exception\ElementTextException
   */
  private function assertTextInRow($row_number, $css_class, $text) {
    $this->assertSession()
      ->elementTextContains('css', "table > tbody > tr:nth-child({$row_number}) td.views-field-{$css_class}", $text);
  }

  /**
   * Cyclic check that text is in specific row.
   *
   * @param string $css_class
   *   Part of the css class of required field.
   * @param array $texts
   *   Array of texts that should be found in the element
   *   and rows' order number.
   *
   * @throws \Behat\Mink\Exception\ElementTextException
   */
  private function assertTextInRowOrder($css_class, array $texts) {
    foreach ($texts as $id => $text) {
      $this->assertTextInRow($id, $css_class, $text);
    }
  }

  /**
   * Adds languages to Drupal.
   *
   * @param array $langcodes
   *   Langcodes that should be added to Drupal.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function addLanguages(array $langcodes) {
    foreach ($langcodes as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
  }

  /**
   * Change language settings for entity types.
   *
   * @param string $category
   *   Entity category (e.g. Content).
   * @param string $subcategory
   *   Entity subcategory (e.g. Article).
   */
  private function enableTranslation($category, $subcategory) {
    $this->drupalPostForm('admin/config/regional/content-language', [
      "entity_types[$category]"                                                   => 1,
      "settings[$category][$subcategory][translatable]"                           => 1,
      "settings[$category][$subcategory][settings][language][language_alterable]" => 1,
    ], t('Save configuration'));
    \Drupal::entityTypeManager()->clearCachedDefinitions();
  }

  /**
   * Set column sorting and order.
   *
   * @param string $orderColumn
   *   Machine name of the column to be sorted.
   * @param string $sort
   *   Sorting order (asc or desc).
   * @param array $default_params
   *   Langcode and Translation target language.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  private function assertOrder($orderColumn, $sort, array $default_params = []) {
    $this->drupalGet($this->viewPath, [
      'query' => [
        'langcode'                    => $default_params[0],
        'translation_target_language' => $default_params[1],
        'translation_outdated'        => 'All',
        'translation_status'          => 'All',
        'order'                       => $orderColumn,
        'sort'                        => $sort,
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp($import_test_views);

    $user = $this->drupalCreateUser([
      'administer content types',
      'edit own comments',
      'administer comments',
      'administer comment types',
      'administer comment fields',
      'administer comment display',
      'post comments',
      'access comments',
      'skip comment approval',
      'access content',
      'administer site configuration',
      'administer nodes',
      'administer views',
      'create article content',
      'edit any article content',
      'administer content translation',
      'translate any entity',
      'create content translations',
      'administer languages',
    ]);

    $this->drupalLogin($user);

    ViewTestData::createTestViews(get_class($this), ['translation_views_test_views']);

    // Add two languages.
    $this->addLanguages(['de', 'fr']);

    // Enable translation for Comment entity type.
    $this->enableTranslation('comment', 'comment');

    // Add 3 nodes.
    $this->drupalPostForm('node/add/article', [
      'title[0][value]'         => 'node 1',
      'created[0][value][date]' => '2018-04-01',
    ], t('Save'));
    $this->drupalPostForm('node/add/article', [
      'title[0][value]'         => 'node 2',
      'created[0][value][date]' => '2018-04-02',
    ], t('Save'));
    $this->drupalPostForm('node/add/article', [
      'title[0][value]'         => 'node 3',
      'created[0][value][date]' => '2018-04-03',
    ], t('Save'));

    // Add comments and translations.
    // Comment 1.
    $edit = [
      'langcode[0][value]'     => 'en',
      'comment_body[0][value]' => $this->loremIpsum,
      'subject[0][value]'      => 'node 1 en comment',
    ];
    $this->drupalPostForm('node/1', $edit, 'Save');
    // Comment 2.
    $edit = [
      'langcode[0][value]'     => 'de',
      'comment_body[0][value]' => $this->loremIpsum,
      'subject[0][value]'      => 'node 1 de comment',
    ];
    $this->drupalPostForm('node/1', $edit, 'Save');
    // Comment 3.
    $edit = [
      'langcode[0][value]'     => 'de',
      'comment_body[0][value]' => $this->loremIpsum,
      'subject[0][value]'      => 'node 2 de comment',
    ];
    $this->drupalPostForm('node/2', $edit, 'Save');
    // Comment 4.
    $edit = [
      'langcode[0][value]'     => 'fr',
      'comment_body[0][value]' => $this->loremIpsum,
      'subject[0][value]'      => 'node 2 fr comment',
    ];
    $this->drupalPostForm('node/2', $edit, 'Save');
    // Comment 5.
    $edit = [
      'langcode[0][value]'     => 'fr',
      'comment_body[0][value]' => $this->loremIpsum,
      'subject[0][value]'      => 'node 3 fr comment',
    ];
    $this->drupalPostForm('node/3', $edit, 'Save');
    // Comment 6.
    $edit = [
      'content_translation[retranslate]' => 1,
      'comment_body[0][value]'           => $this->loremIpsum,
      'subject[0][value]'                => 'node 3 en from fr comment',
    ];
    $this->drupalPostForm('comment/5/translations/add/fr/en', $edit, 'Save');
  }

  /**
   * Tests that the fields show all required information.
   */
  public function testFields() {
    $this->drupalGet($this->viewPath);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertTextInRow(1, 'subject', 'node 1 en comment');
    $this->assertTextInRow(1, 'langcode', 'English');
    $this->assertTextInRow(1, 'translation-target-language', 'English');
    $this->assertTextInRow(1, 'translation-default', 'Yes');
    $this->assertTextInRow(1, 'translation-changed', ':');
    $this->assertTextInRow(1, 'translation-count', '0');
    $this->assertTextInRow(1, 'translation-outdated', 'No');
    $this->assertTextInRow(1, 'translation-source', 'Yes');
    $this->assertTextInRow(1, 'translation-status', 'Translated');
    $this->assertTextInRow(1, 'translation-operations', 'Edit');
  }

  /**
   * Tests columns' sorting.
   */
  public function testSorting() {
    // Title.
    $this->assertOrder('subject', 'asc', ['de', 'fr']);
    $this->assertTextInRowOrder('subject', [
      1 => 'node 1 de comment',
      2 => 'node 2 de comment',
    ]);
    $this->assertSession()->pageTextNotContains('node 1 en comment');
    $this->assertSession()->pageTextNotContains('node 2 fr comment');
    $this->assertSession()->pageTextNotContains('node 3 fr comment');
    $this->assertSession()->pageTextNotContains('node 3 en from fr comment');

    $this->assertOrder('subject', 'desc', ['de', 'fr']);
    $this->assertTextInRowOrder('subject', [
      2 => 'node 1 de comment',
      1 => 'node 2 de comment',
    ]);
    $this->assertSession()->pageTextNotContains('node 1 en comment');
    $this->assertSession()->pageTextNotContains('node 2 fr comment');
    $this->assertSession()->pageTextNotContains('node 3 fr comment');
    $this->assertSession()->pageTextNotContains('node 3 en from fr comment');

    // Translation language.
    $this->assertOrder('langcode', 'asc', ['All', 'de']);
    $this->assertTextInRowOrder('langcode', [
      1 => 'German',
      2 => 'German',
      3 => 'English',
      4 => 'English',
      5 => 'French',
      6 => 'French',
    ]);

    $this->assertOrder('langcode', 'desc', ['All', 'de']);
    $this->assertTextInRowOrder('langcode', [
      6 => 'German',
      5 => 'German',
      4 => 'English',
      3 => 'English',
      2 => 'French',
      1 => 'French',
    ]);

    // Target language equals default language.
    $this->assertOrder('translation_default', 'asc', ['All', 'fr']);
    $this->assertTextInRowOrder('translation-default', [
      1 => 'No',
      2 => 'No',
      3 => 'No',
      4 => 'Yes',
      5 => 'Yes',
      6 => 'Yes',
    ]);

    $this->assertOrder('translation_default', 'desc', ['All', 'fr']);
    $this->assertTextInRowOrder('translation-default', [
      6 => 'No',
      5 => 'No',
      4 => 'No',
      3 => 'Yes',
      2 => 'Yes',
      1 => 'Yes',
    ]);

    // Translation changed time.
    $this->assertOrder('translation_changed', 'asc', ['All', 'fr']);
    $this->assertTextInRowOrder('translation-changed', [
      4 => ':',
      5 => ':',
      6 => ':',
    ]);

    $this->assertOrder('translation_changed', 'desc', ['All', 'fr']);
    $this->assertTextInRowOrder('translation-changed', [
      1 => ':',
      2 => ':',
      3 => ':',
    ]);

    // Translation counter.
    $this->assertOrder('translation_count', 'asc', ['en', 'fr']);
    $this->assertTextInRowOrder('translation-count', [
      1 => '0',
      2 => '1',
    ]);

    $this->assertOrder('translation_count', 'desc', ['en', 'fr']);
    $this->assertTextInRowOrder('translation-count', [
      2 => '0',
      1 => '1',
    ]);

    // Translation outdated.
    $this->assertOrder('translation-outdated', 'asc', ['en', 'fr']);
    $this->assertTextInRowOrder('translation-outdated', [
      1 => 'No',
      2 => 'Yes',
    ]);

    $this->assertOrder('translation-outdated', 'desc', ['en', 'fr']);
    $this->assertTextInRowOrder('translation-outdated', [
      1 => 'No',
      2 => 'Yes',
    ]);

    // Source translation of target language equals row language.
    $this->assertOrder('translation_source', 'asc', [
      'All',
      '***LANGUAGE_site_default***',
    ]);
    $this->assertTextInRowOrder('translation-source', [
      1 => 'No',
      2 => 'No',
      3 => 'No',
      4 => 'No',
      5 => 'Yes',
      6 => 'Yes',
    ]);

    $this->assertOrder('translation_source', 'desc', [
      'All',
      '***LANGUAGE_site_default***',
    ]);
    $this->assertTextInRowOrder('translation-source', [
      6 => 'No',
      5 => 'No',
      4 => 'No',
      3 => 'No',
      2 => 'Yes',
      1 => 'Yes',
    ]);

    // Translation status.
    $this->assertOrder('translation_status', 'asc', [
      'All',
      '***LANGUAGE_site_default***',
    ]);
    $this->assertTextInRowOrder('translation-status', [
      1 => 'Not translated',
      2 => 'Not translated',
      3 => 'Not translated',
      4 => 'Translated',
      5 => 'Translated',
      6 => 'Translated',
    ]);

    $this->assertOrder('translation_status', 'desc', [
      'All',
      '***LANGUAGE_site_default***',
    ]);
    $this->assertTextInRowOrder('translation-status', [
      6 => 'Not translated',
      5 => 'Not translated',
      4 => 'Not translated',
      3 => 'Translated',
      2 => 'Translated',
      1 => 'Translated',
    ]);

  }

  /**
   * Tests that filters are working correctly.
   */
  public function testFilters() {

    // Translation language.
    $this->drupalGet($this->viewPath, [
      'query' => [
        'langcode' => 'en',
      ],
    ]);

    $this->assertTextInRowOrder('subject', [
      1 => 'node 1 en comment',
      2 => 'node 3 en from fr comment',
    ]);
    $this->assertSession()->pageTextNotContains('node 1 de comment');
    $this->assertSession()->pageTextNotContains('node 2 de comment');
    $this->assertSession()->pageTextNotContains('node 2 fr comment');
    $this->assertSession()->pageTextNotContains('node 3 fr comment');

    // Translation language & Target language.
    $this->drupalGet($this->viewPath, [
      'query' => [
        'langcode'                    => 'en',
        'translation_target_language' => 'fr',
      ],
    ]);
    $this->assertTextInRowOrder('subject', [
      1 => 'node 1 en comment',
      2 => 'node 3 en from fr comment',
    ]);
    $this->assertSession()->pageTextNotContains('node 1 de comment');
    $this->assertSession()->pageTextNotContains('node 2 de comment');
    $this->assertSession()->pageTextNotContains('node 2 fr comment');
    $this->assertSession()->pageTextNotContains('node 3 fr comment');

    // Translation language & Target language & Translation outdated.
    $this->drupalGet($this->viewPath, [
      'query' => [
        'langcode'                    => 'en',
        'translation_target_language' => 'fr',
        'translation_outdated'        => '1',
      ],
    ]);
    $this->assertTextInRowOrder('subject', [
      1 => 'node 3 en from fr comment',
    ]);
    $this->assertSession()->pageTextNotContains('node 1 en comment');
    $this->assertSession()->pageTextNotContains('node 1 de comment');
    $this->assertSession()->pageTextNotContains('node 2 de comment');
    $this->assertSession()->pageTextNotContains('node 2 fr comment');
    $this->assertSession()->pageTextNotContains('node 3 fr comment');

    // Translation status #1.
    $this->drupalGet($this->viewPath, [
      'query' => [
        'translation_status' => '0',
      ],
    ]);
    $this->assertTextInRowOrder('subject', [
      1 => 'node 1 de comment',
      2 => 'node 2 de comment',
      3 => 'node 2 fr comment',
    ]);
    $this->assertSession()->pageTextNotContains('node 1 en comment');
    $this->assertSession()->pageTextNotContains('node 3 fr comment');
    $this->assertSession()->pageTextNotContains('node 3 en from fr comment');

    // Translation status #2.
    $this->drupalGet($this->viewPath, [
      'query' => [
        'translation_status' => '1',
      ],
    ]);
    $this->assertTextInRowOrder('subject', [
      1 => 'node 1 en comment',
      2 => 'node 3 en from fr comment',
      3 => 'node 3 fr comment',
    ]);
    $this->assertSession()->pageTextNotContains('node 1 de comment');
    $this->assertSession()->pageTextNotContains('node 2 de comment');
    $this->assertSession()->pageTextNotContains('node 2 fr comment');
  }

}
