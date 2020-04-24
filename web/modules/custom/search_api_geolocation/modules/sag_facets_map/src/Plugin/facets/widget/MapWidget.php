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

    $build = parent::build($facet);
    $build['#attributes']['id'] = $facet->id();
    $build['#attributes']['class'][] = 'js-facets-map-links';
    $build['#attributes']['style'][] = 'display: none';
    $build['#attached']['library'][] = 'sag_facets_map/facets-map';

    $build['#attached']['drupalSettings']['facets']['map'] = [
      'facet_id' => $facet->id(),
      'facet_url_name' => $facet->getUrlAlias(),
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
