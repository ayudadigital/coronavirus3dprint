<?php

namespace Drupal\user_extra_field\Plugin\ExtraField\Display;

use Drupal;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;

/**
 * User Profile Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "user_profile_compact",
 *   label = @Translation("User Profile in compact view mode"),
 *   bundles = {
 *     "node.services",
 *     "user.user",
 *   }
 * )
 */
class CompactProfile extends ExtraFieldDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {

    $elements = [];
    $view_mode = 'compact';
    $entity_type = $entity->getEntityTypeId();

    /** @var \Drupal\node\NodeInterface $entity */
    if($entity_type == 'user'){
      $elements = Drupal::entityTypeManager()->getViewBuilder('user')->view($entity, $view_mode);
    }
    elseif($entity_type == 'node'){
      $elements = Drupal::entityTypeManager()->getViewBuilder('user')->view($entity->getOwner(), $view_mode);
    }

    return $elements;
  }

}
