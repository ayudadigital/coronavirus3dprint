<?php

namespace Drupal\admin_login_path\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Define the routes we want to use the admin theme.
    $login_routes = ['user.login', 'user.register', 'user.pass'];
    foreach ($collection->all() as $name => $route) {
      if (in_array($name, $login_routes)) {
        $route->setOption('_admin_route', TRUE);
      }
    }
  }

}
