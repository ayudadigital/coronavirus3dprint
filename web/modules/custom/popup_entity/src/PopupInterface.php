<?php

namespace Drupal\popup_entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Popup entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup popup_entity
 */
interface PopupInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
