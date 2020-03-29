<?php

namespace Drupal\extra_field_example\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;

/**
 * Example Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "all_nodes",
 *   label = @Translation("For all nodes"),
 *   bundles = {
 *     "node.*"
 *   },
 *   weight = -30,
 *   visible = true
 * )
 */
class ExampleAllNodes extends ExtraFieldDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {

    $elements = ['#markup' => 'This is output from ExampleAllNodes'];

    return $elements;
  }

}
