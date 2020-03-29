<?php

namespace Drupal\extra_field\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;

/**
 * Base class for Extra field Display plugins.
 */
abstract class ExtraFieldDisplayBase extends PluginBase implements ExtraFieldDisplayInterface {

  /**
   * The field's parent entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The view mode the entity is rendered in.
   *
   * @var string
   */
  protected $viewMode;

  /**
   * The entity view display.
   *
   * Contains the display options configured for the entity components.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $entityViewDisplay;

  /**
   * {@inheritdoc}
   */
  public function setEntity(ContentEntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityViewDisplay(EntityViewDisplayInterface $display) {
    $this->entityViewDisplay = $display;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityViewDisplay() {
    return $this->entityViewDisplay;
  }

  /**
   * {@inheritdoc}
   */
  public function setViewMode($viewMode) {
    $this->viewMode = $viewMode;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewMode() {
    return $this->viewMode;
  }

}
