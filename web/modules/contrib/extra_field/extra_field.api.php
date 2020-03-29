<?php

/**
 * @file
 * Extra Field API documentation.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Defines Extra Field Display types.
 *
 * Extra Field API Displays allow developers to add display-only fields to
 * entities. The entities and entity bundle(s) for which the plugin is available
 * is determined with the the 'bundles' key.
 * Site builders can use extra fields as normal field on an entity display page.
 *
 * Extra Field Displays are Plugins managed by the
 * \Drupal\extra_field\Plugin\ExtraFieldDisplayManager class.
 * A Display is a plugin annotated with class
 * \Drupal\extra_field\Annotation\ExtraFieldDisplay that implements
 * \Drupal\extra_field\Plugin\ExtraFieldDisplayInterface (in most cases, by
 * subclassing one of the base classes). Extra Field Display plugins need to
 * be in the namespace \Drupal\{your_module}\Plugin\ExtraField\Display.
 *
 * @see plugin_api
 */

/**
 * Perform alterations on Extra Field Displays.
 *
 * @param array $info
 *   An array of information on existing Extra Field Displays, as collected by
 *   the annotation discovery mechanism.
 */
function hook_extra_field_display_info_alter(array &$info) {
  // Let a plugin also be used for all taxonomy terms.
  $info['all_nodes']['bundles'][] = 'taxonomy_term.*';
}

/**
 * @} End of "addtogroup hooks".
 */
