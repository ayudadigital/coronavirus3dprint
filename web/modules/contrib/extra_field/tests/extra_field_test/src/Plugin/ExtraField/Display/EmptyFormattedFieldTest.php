<?php

namespace Drupal\extra_field_test\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field Display for a field without content.
 *
 * @ExtraFieldDisplay(
 *   id = "empty_formatted_test",
 *   label = @Translation("Formatted extra field without content"),
 *   bundles = {
 *     "node.first_node_type",
 *   },
 *   visible = true
 * )
 */
class EmptyFormattedFieldTest extends ExtraFieldDisplayFormattedBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {

    $elements = ['#cache' => ['max-age' => 0]];
    // This field has no content.
    $this->isEmpty = TRUE;

    return $elements;
  }

}
