<?php

namespace Drupal\views\Tests;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ViewTranslationTest.
 *
 * @group views
 */
class ViewTranslationTest extends ViewTestBase {

  /**
   * {@inheritdoc}
   */
  public static $testViews = ['test_view'];

  /**
   * {@inheritdoc}
   */
  public static $modules = ['locale', 'language', 'config_translation', 'views'];

  /**
   * Other language than default.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $otherLanguage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function setUp($import_test_views = TRUE) {
    parent::setUp($import_test_views);

    $this->enableViewsTestModule();

    ConfigurableLanguage::create(['id' => 'ro'])->save();
    $this->rebuildContainer();

    $this->languageManager = \Drupal::languageManager();
    $this->otherLanguage = $this->languageManager->getLanguage('ro');
  }

  /**
   * Test if the view route title gets translated.
   */
  public function testViewRouteTranslation() {
    $view = Views::getView('test_view');

    // Create a test display, add path and default language title.
    $view->storage->addDisplay('page');
    $displays = $view->storage->get('display');
    $displays['default']['display_options']['title'] = 'Title EN';
    $displays['page_1']['display_options']['path'] = 'test-view';
    $view->storage->set('display', $displays);
    $view->save();

    // We need to rebuild the routes to discover the route to the
    // view display.
    /** @var \Drupal\Core\Routing\RouteBuilderInterface $routeBuilder */
    $routeBuilder = \Drupal::service('router.builder');
    $routeBuilder->rebuild();

    /** @var \Drupal\Core\Routing\RouteProviderInterface $routeProvider */
    $routeProvider = \Drupal::service('router.route_provider');
    $route = $routeProvider->getRouteByName('view.test_view.page_1');

    /** @var \Drupal\Core\Controller\TitleResolver $titleResolver */
    $titleResolver = \Drupal::service('title_resolver');

    $request = Request::create('/test-view');
    $request->attributes->add([
      'view_id' => $view->id(),
      'display_id' => 'page_1',
    ]);

    // First test if the routes title gets set correctly in the
    // default language.
    $this->assertEqual($titleResolver->getTitle($request, $route),
      'Title EN', 'The default title is in the default language.');

    // Override language for config to translate view title.
    $this->languageManager->setConfigOverrideLanguage($this->otherLanguage);
    // Add translation for the display.
    $displays = $view->storage->get('display');
    $displays['default']['display_options']['title'] = 'Title RO';
    $view->storage->set('display', $displays);
    $view->save();

    // Add the language in which we translated to the request.
    $request->setLocale($this->otherLanguage->getId());

    $this->assertEqual($titleResolver->getTitle($request, $route),
      'Title RO', 'The route title gets translated when language set.');
  }

}
