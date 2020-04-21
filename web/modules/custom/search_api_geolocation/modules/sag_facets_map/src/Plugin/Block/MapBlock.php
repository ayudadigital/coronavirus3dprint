<?php

namespace Drupal\sag_facets_map\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Map' Block.
 *
 * @Block(
 *   id = "map_facets_block",
 *   admin_label = @Translation("Map Facets block"),
 *   category = @Translation("Map"),
 * )
 */
class MapBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#markup' => '<div id="sag-facets-map-block" class="facets-map"></div>',
    );
  }

}
