<?php

namespace Drupal\extra_field\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;

/**
 * Defines an interface for Extra Field Display plugins.
 */
interface ExtraFieldDisplayInterface extends PluginInspectionInterface {

  /**
   * Builds a renderable array for the field.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The field's host entity.
   *
   * @return array
   *   Renderable array.
   */
  public function view(ContentEntityInterface $entity);

  /**
   * Stores the field's parent entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that hosts the field.
   */
  public function setEntity(ContentEntityInterface $entity);

  /**
   * Returns the field's parent entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The entity that hosts the field.
   */
  public function getEntity();

  /**
   * Stores the entity view display.
   *
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display holding the display options configured for the
   *   entity components. As provided by hook_entity_view()
   */
  public function setEntityViewDisplay(EntityViewDisplayInterface $display);

  /**
   * Returns the entity view display object of the field's host entity.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   *   The entity view display object.
   */
  public function getEntityViewDisplay();

  /**
   * Stores the entity view mode.
   *
   * @param string $viewMode
   *   The view mode the entity is rendered in. As provided by
   *   hook_entity_view().
   */
  public function setViewMode($viewMode);

  /**
   * Returns the entity view mode object of the field's host entity.
   *
   * @return string
   *   The view mode the field is being rendered in.
   */
  public function getViewMode();

}
