<?php

namespace Drupal\extra_field_example\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;

/**
 * Example Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "article_only",
 *   label = @Translation("Only for articles"),
 *   bundles = {
 *     "node.article",
 *   }
 * )
 */
class ExampleArticle extends ExtraFieldDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {

    $elements = ['#markup' => 'This is output from ExampleArticle'];

    return $elements;
  }

}
