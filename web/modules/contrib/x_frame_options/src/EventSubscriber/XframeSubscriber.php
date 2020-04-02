<?php

namespace Drupal\x_frame_options_configuration\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Subscribing an event.
 */
class XframeSubscriber implements EventSubscriberInterface {

  /**
   * Drupal's settings manager.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * XframeSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('x_frame_options_configuration.settings');
  }

  /**
   * Executes actions on the respose event.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   Filter Response Event object.
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    // Add the x-frame-options response header with the configured directive.
    $directive = $this->config->get('x_frame_options_configuration.directive', 0);
    $allow_from_uri = Html::escape($this->config->get('x_frame_options_configuration.allow-from-uri', ''));
    $x_frame_options = Html::escape($directive) . (($directive == 'ALLOW-FROM') ? " " . UrlHelper::stripDangerousProtocols($allow_from_uri) : '');
    if ($x_frame_options) {
      $response = $event->getResponse();
      $response->headers->set('X-Frame-Options', $x_frame_options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Adds the event in the list of KernelEvents::RESPONSE with priority -10.
    $events[KernelEvents::RESPONSE][] = ['onKernelResponse', -10];
    return $events;
  }

}
