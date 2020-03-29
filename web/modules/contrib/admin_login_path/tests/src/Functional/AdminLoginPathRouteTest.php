<?php

namespace Drupal\Tests\admin_login_path\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the functionality of the Admin Login Path route subscriber.
 *
 * @group admin_login_path
 */
class AdminLoginPathRouteTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var string[]
   */
  public static $modules = ['admin_login_path'];

  /**
   * Asserts that login routes are correctly marked as admin routes.
   */
  public function testAdminRoute() {
    $login_routes = ['user.login', 'user.register', 'user.pass'];

    foreach ($login_routes as $route_name) {
      $route = \Drupal::service('router.route_provider')->getRouteByName($route_name);
      $is_admin = \Drupal::service('router.admin_context')->isAdminRoute($route);
      $this->assertTrue($is_admin, format_string('Admin route correctly marked for "@title" page.', ['@title' => $route->getDefault('_title')]));
    }
  }

}
