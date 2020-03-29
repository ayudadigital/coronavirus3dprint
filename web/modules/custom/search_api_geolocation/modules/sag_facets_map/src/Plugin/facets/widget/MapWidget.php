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
    /** @var \Drupal\facets\Result\Result[] $results */
    $results = $facet->getResults();

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

    //set geohash for geoclusters markers
    $geo_hash = [];
    foreach ($results as $result) {
      if($result->getCount() != 0){
        $geo_hash[] = array(
          'key' => $result->getRawValue(),
          'doc_count' => $result->getCount(),
        );
      }
    }

    if(!isset($lat) || !is_numeric($lat)){
      $lat = 0;
    }
    if(!isset($lng) || !is_numeric($lng)){
      $lng = 0;
    }
    if(!isset($zoom_map) || !is_numeric($zoom_map)){
      $zoom_map = 1;
    }

    //set empty new url
    /** @var \Drupal\facets\Plugin\facets\processor\UrlProcessorHandler $url_processor_handler */
    $url_processor_handler = $facet->getProcessors()['url_processor_handler'];
    $url_processor = $url_processor_handler->getProcessor();
    $filter_key = $url_processor->getFilterKey();

    //get current url
    if(!empty($results[0])){
      $url = $results[0]->getUrl();
      $query = $url->getOption('query');

      // Remove all the query filters for the field of the facet.
      if (isset($query[$filter_key])) {
        foreach ($query[$filter_key] as $id => $filter) {
          if (strpos($filter . $url_processor->getSeparator(), $facet->getUrlAlias()) === 0) {
            unset($query[$filter_key][$id]);
          }
        }
      }

      $query[$filter_key][] = $facet->getUrlAlias() . $url_processor->getSeparator() . '(geom:__GEOM__)';
      $url->setOption('query', $query);
      $results[0]->setUrl($url);
    }

    $build['map'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['facets-map'],
        'id' => $facet->id(),
      ],
    ];
    $build['#attached']['library'][] = 'sag_facets_map/facets-map';
    $build['#attached']['drupalSettings']['facets']['map'] = [
      'facet_id' => $facet->id(),
      'lat' => $lat,
      'lng' => $lng,
      'zoom' => $zoom_map,
      'url' => !empty($results[0]) ? $results[0]->getUrl()->toString() : '',
      'geo_hash' => $geo_hash,
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
