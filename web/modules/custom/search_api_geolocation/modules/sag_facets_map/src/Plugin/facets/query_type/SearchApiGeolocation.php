<?php

namespace Drupal\sag_facets_map\Plugin\facets\query_type;

use Drupal\facets\QueryType\QueryTypePluginBase;
use Drupal\facets\Result\Result;
use Drupal\elasticsearch_connector\Plugin\search_api\backend\GeoHashGrid;

/**
 * Provides support for location facets within the Search API scope.
 *
 * This query type supports SpatialRecursivePrefixTree data type. This specific
 * implementation of the query type supports a generic solution of
 * adding an interactive map facets showing clustered heatmap.
 *
 * @FacetsQueryType(
 *   id = "search_api_geolocation",
 *   label = @Translation("Geolocation"),
 * )
 */
class SearchApiGeolocation extends QueryTypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $query = $this->query;
    $field_identifier = $this->facet->getFieldIdentifier();

    $filter_name = $field_identifier;

    $active_facet = $this->facet->getActiveItems();
    $geo_params_string = !isset($active_facet['0']) ? '' : $active_facet['0'];

    $geo_params = str_replace(['(geom:', ')'], ['', ''], $geo_params_string);
    $geo_params = explode('/', $geo_params);

    $geohash = !isset($geo_params['0']) ? 0 : $geo_params['0'];
    $zoom_map = !isset($geo_params['1']) ? 1 : $geo_params['1'];
    $top_left_lat_limit = !isset($geo_params['2']) ? 0 : $geo_params['2'];
    $top_left_lng_limit = !isset($geo_params['3']) ? 0 : $geo_params['3'];
    $bottom_right_lat_limit = !isset($geo_params['4']) ? 0 : $geo_params['4'];
    $bottom_right_lng_limit = !isset($geo_params['5']) ? 0 : $geo_params['5'];

    //Get the zoom level
    $precision = 3;
    if($zoom_map >= 5 && $zoom_map <= 8){
      $precision =4;
    }
    elseif($zoom_map >= 9 && $zoom_map <= 11){
      $precision =5;
    }
    elseif($zoom_map >= 12 && $zoom_map <= 14){
      $precision =6;
    }
    elseif($zoom_map >= 15 && $zoom_map <= 17){
      $precision =7;
    }
    elseif($zoom_map >= 18){
      $precision =8;
    }

    if(!empty($geohash)){
      $geo_bounding_box = array(
        "geolocation" => array(
          "top_left" => $geohash,
          "bottom_right" => $geohash,
        ),
      );
      $query->setOption('geo_bounding_box', $geo_bounding_box);
    }
    elseif(!empty($top_left_lat_limit) && !empty($top_left_lng_limit) &&
      !empty($bottom_right_lat_limit) && !empty($bottom_right_lng_limit) && $precision > 3){
      $geo_bounding_box = array(
        "geolocation" => array(
          "top_left" => array(
            'lat'=> $top_left_lat_limit,
            'lon'=> $top_left_lng_limit,
          ),
          "bottom_right" => array(
            'lat'=> $bottom_right_lat_limit,
            'lon'=> $bottom_right_lng_limit,
          ),
        ),
      );
      $query->setOption('geo_bounding_box', $geo_bounding_box);
    }

    $aggs_geohash_grid_values[$filter_name] = array(
      "field" => $this->facet->getFieldIdentifier(), //field on which the aggregation need to work
      "precision" => $precision, //zoom can have values from 1 to 8
    );
    $query->setOption('aggs_geohash_grid', $aggs_geohash_grid_values);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (!empty($this->results)) {

      $facet_results = [];
      foreach ($this->results as $key => $result) {
        if (isset($result['count'])) {
          $count = $result['count'];
          $result_filter = $result['filter'];
          $result = new Result($this->facet, $result_filter, $result_filter, $count);
          $facet_results[] = $result;
        }
      }
      $this->facet->setResults($facet_results);
    }

    return $this->facet;
  }

}
