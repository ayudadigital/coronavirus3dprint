<?php

namespace Drupal\entity_content_visibility\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringLongItem;

/**
 * @FieldType(
 *   id = "entity_content_visibility",
 *   label = @Translation("Entity Content Visibility"),
 *   default_widget = "entity_content_visibility",
 *   no_ui = TRUE
 * )
 */
class EntityContentVisibilityItem extends StringLongItem {

}
