<?php

namespace Drupal\sag_facets_map\Plugin\facets\widget;

use Drupal\facets\FacetInterface;
use Drupal\facets\Widget\WidgetPluginBase;

/**
 * A widget class that provides a map interface to narrow down facet results.
 *
 * @FacetsWidget(
 *   id = "map_widget",
 *   label = @Translation("Interactive map showing the clustered heatmap"),
 *   description = @Translation("A configurable widget that builds an location array with results."),
 * )
 */
class MapWidget extends WidgetPluginBase {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {

    // add default center point of the map
    $active_item = $facet->getActiveItems();
    if(!empty($active_item[0])) {
      $geo_params = urldecode($active_item[0]);
      if (!empty($geo_params)) {
        $geo_params = str_replace(['(geom:', ')'], ['', ''], $geo_params);
        $geo_params = explode('/', $geo_params);
        if (!empty($geo_params[0]) && !empty($geo_params[1]) && !empty($geo_params[2])) {
          $lat = $geo_params[0];
          $lng = $geo_params[1];
          $zoom_map = $geo_params[2];
        }
      }
    }

    if(!isset($lat) || !is_numeric($lat)){
      $lat = 0;
    }
    if(!isset($lng) || !is_numeric($lng)){
      $lng = 0;
    }
    if(!isset($zoom_map) || !is_numeric($zoom_map)){
      $zoom_map = 0;
    }


    $build = parent::build($facet);
    $build['#attributes']['id'] = $facet->id();
    $build['#attributes']['class'][] = 'js-facets-map-links';
    $build['#attributes']['style'][] = 'display: none';
    $build['#attached']['library'][] = 'sag_facets_map/facets-map';

    $build['#attached']['drupalSettings']['facets']['map'] = [
      'facet_id' => $facet->id(),
      'facet_url_name' => $facet->getUrlAlias(),
      'lat' => $lat,
      'lng' => $lng,
      'zoom' => $zoom_map,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType() {
    return 'geolocation';
  }

}
