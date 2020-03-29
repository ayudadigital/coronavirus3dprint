<?php

namespace Drupal\extra_field_test\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field Display for a field with multiple items output.
 *
 * @ExtraFieldDisplay(
 *   id = "multiple_text_test",
 *   label = @Translation("Extra field with multiple text item"),
 *   bundles = {
 *     "node.first_node_type",
 *   },
 *   visible = true
 * )
 */
class MultipleItemsFieldTest extends ExtraFieldDisplayFormattedBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {

    $elements = [
      ['#markup' => 'Aap'],
      ['#markup' => 'Noot'],
      ['#markup' => 'Mies'],
      ['#markup' => 'Wim'],
      ['#markup' => 'Zus'],
      ['#markup' => 'Jet'],
    ];

    return $elements;
  }

}
