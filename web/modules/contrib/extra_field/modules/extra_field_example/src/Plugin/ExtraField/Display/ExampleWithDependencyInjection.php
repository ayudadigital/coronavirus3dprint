<?php

namespace Drupal\extra_field_example\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Example Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "article_only",
 *   label = @Translation("Dependency Injection example"),
 *   bundles = {
 *     "node.article",
 *   }
 * )
 */
class ExampleWithDependencyInjection extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a ExtraFieldDisplayFormattedBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {

    // Some output to demonstrate the injected service.
    $scheme = $this->currentRequest->getScheme();
    $elements = ['#markup' => $this->t('Request scheme: @scheme', ['@scheme' => $scheme])];

    return $elements;
  }

}
