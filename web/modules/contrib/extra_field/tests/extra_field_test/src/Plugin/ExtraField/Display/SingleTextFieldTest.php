<?php

namespace Drupal\extra_field_test\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field Display for a field with single item output.
 *
 * @ExtraFieldDisplay(
 *   id = "single_text_test",
 *   label = @Translation("Extra field formatted as text field"),
 *   bundles = {
 *     "node.first_node_type",
 *   },
 *   visible = true
 * )
 */
class SingleTextFieldTest extends ExtraFieldDisplayFormattedBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {

    $elements = ['#markup' => 'Output from SingleTextFieldTest'];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return 'Single text';
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'inline';
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldType() {
    return 'single_text';
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldName() {
    return 'field_single_text';
  }

}
