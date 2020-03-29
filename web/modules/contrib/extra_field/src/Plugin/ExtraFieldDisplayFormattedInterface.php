<?php

namespace Drupal\extra_field\Plugin;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines an interface for Extra Field Display plugins.
 */
interface ExtraFieldDisplayFormattedInterface extends ExtraFieldDisplayInterface {

  /**
   * Returns the renderable array of the field item(s).
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The field's host entity.
   *
   * @return array
   *   A renderable array of field elements. If this contains children, the
   *   field output will be rendered as a multiple value field with each child
   *   as a field item.
   */
  public function viewElements(ContentEntityInterface $entity);

  /**
   * The label of the field.
   *
   * If applicable, the code has to take care of the translatability.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The field label.
   */
  public function getLabel();

  /**
   * How to display the field label will be displayed.
   *
   * @return string
   *   Options: 'above', 'inline', 'hidden', 'visually_hidden'.
   */
  public function getLabelDisplay();

  /**
   * The type of field.
   *
   * Used in the 'field--type-...' wrapper class.
   *
   * @return string
   *   The field type.
   */
  public function getFieldType();

  /**
   * The machine name of the field.
   *
   * Used in the 'field--name-...' wrapper class.
   *
   * @return string
   *   The field name.
   */
  public function getFieldName();

  /**
   * Check if the extra field has data.
   *
   * @return bool
   *   True if the field has no actual data to render. False if it only contains
   *   non-visible render elements such as #cache and #attached.
   */
  public function isEmpty();

  /**
   * Gets the langcode of the field values.
   *
   * @return string
   *   The langcode.
   */
  public function getLangcode();

  /**
   * The field is translatable.
   *
   * @return bool
   *   Whether the field's content is translatable.
   */
  public function isTranslatable();

}
