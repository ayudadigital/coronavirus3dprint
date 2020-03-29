<?php

namespace Drupal\user_extra_field\Plugin\ExtraField\Display;

use Drupal;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;

/**
 * User Profile Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "user_profile_teaser",
 *   label = @Translation("User Profile in teaser view mode"),
 *   bundles = {
 *     "node.services",
 *   }
 * )
 */
class TeaserProfile extends ExtraFieldDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {

    $view_mode = 'teaser';

    /** @var \Drupal\node\NodeInterface $entity */
    $elements = Drupal::entityTypeManager()->getViewBuilder('user')->view($entity->getOwner(), $view_mode);

    return $elements;
  }

}
