<?php

namespace Drupal\entity_content_visibility;


use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Plugin\Context\ContextHandler;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityContentVisibilityCache {

  public static function createFromID(ContainerInterface $container, $entity_type, $id) {
    $entity_manager = $container->get('entity.manager');
    $entity_content = $entity_manager->getStorage($entity_type)->load($id);

    return new self(
      $entity_content,
      $container->get('plugin.manager.condition'),
      $container->get('context.repository'),
      $container->get('context.handler')
    );
  }

  /** @var  ConditionManager */
  private $condition_manager;

  /** @var  ContextRepositoryInterface */
  private $context_repository;

  /** @var  ContextHandler */
  private $context_handler;

  private $entity_content;

  /** @var  ConditionInterface[] */
  private $conditions;

  private function __construct($entity_content, ConditionManager $condition_manager, ContextRepositoryInterface $context_repository, ContextHandler $context_handler) {
    $this->entity_content = $entity_content;
    $this->condition_manager = $condition_manager;
    $this->context_repository = $context_repository;
    $this->context_handler = $context_handler;

    $this->conditions = $this->buildConditions();
  }

  public function getCacheContexts() {
    $cache_contexts = array();
    foreach($this->conditions as $condition) {
      $cache_contexts = Cache::mergeContexts($cache_contexts, $condition->getCacheContexts());
    }
    return $cache_contexts;
  }

  public function getCacheTags() {
    $cache_tags = array();
    foreach($this->conditions as $condition) {
      $cache_tags = Cache::mergeTags($cache_tags, $condition->getCacheTags());
    }
    return $cache_tags;
  }

  public function getCacheMaxAge() {
    $cache_max_age = Cache::PERMANENT;
    foreach($this->conditions as $condition) {
      $cache_max_age = Cache::mergeMaxAges($cache_max_age, $condition->getCacheMaxAge());
    }
    return $cache_max_age;
  }

  private function buildConditions() {
    $conditions = array();
    $visibility = unserialize($this->entity_content->get('visibility')->value);
    if($visibility) {
      foreach ($visibility as $condition_id => $condition_configuration) {
        /** @var ConditionInterface $condition */
        $condition = $this->condition_manager->createInstance($condition_id, $condition_configuration);
        if ($condition instanceof ContextAwarePluginInterface) {
          $contexts = $this->context_repository->getRuntimeContexts(array_values($condition->getContextMapping()));
          try {
            $this->context_handler->applyContextMapping($condition, $contexts);
          } catch (ContextException $e) {

          }
        }
        $conditions[$condition_id] = $condition;
      }
    }
    return $conditions;
  }

}
