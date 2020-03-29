<?php

namespace Drupal\search_api_geolocation\Plugin\search_api\backend;

use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultSetInterface;
use Drupal\elasticsearch_connector\Plugin\search_api\backend\SearchApiElasticsearchBackend;

/**
 * Alter Elastic Search backend
 */
class Geolocation extends SearchApiElasticsearchBackend {

  /**
   * Adds filters to Elastic Search query.
   *
   * {@inheritdoc}
   */
  protected function preQueryParams(QueryInterface $query, &$params) {
    parent::preQueryParams($query, $params);

    // Add the aggs_geohash_grid to the request.
    if ($query->getOption('aggs_geohash_grid')) {
      $aggs_geohash_grid = $query->getOption('aggs_geohash_grid');

      foreach ($aggs_geohash_grid as $field_name => $field_value){
        $params['body']['aggs'] = [
          $field_name => [
            'geohash_grid' => [
              'field' => $field_value['field'],
              'precision' => $field_value['precision'],
            ]
          ]
        ];
      }

    }

    if(!empty($query->getOption('geo_bounding_box'))){
      $geo_bounding_box = $query->getOption('geo_bounding_box');
      $params['body']['query']['bool']['filter']['bool']['filter']['geo_bounding_box'] = $geo_bounding_box;
    }

  }

  /**
   * Allow custom changes before sending a search query to Elasticsearch.
   *
   * This allows subclasses to apply custom changes before the query is sent to
   * Elasticsearch.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   The \Drupal\search_api\Query\Query object representing the executed
   *   search query.
   */
  protected function preQuery(QueryInterface $query) {
    // Add the aggs_geohash_grid to the request.
    if ($query->getOption('aggs_geohash_grid')) {
      $aggs = $query->getOption('aggs_geohash_grid');
      foreach ($aggs as $key => $facet) {
        $object = new GeoHashGrid($facet['field'], $facet['field'], $facet['precision']);
        $this->client->aggregations()->setAggregation($object);
      }
    }
  }

  /**
   * Adds aggs of Elastic Search result.
   *
   * @param \Drupal\search_api\Query\ResultSetInterface $results
   *   Results set.
   *
   * {@inheritdoc}
   */
  protected function postQuery(ResultSetInterface &$results, QueryInterface $query, $response) {
    parent::postQuery($results, $query, $response);

    if ($query->getOption('aggs_geohash_grid')) {
      $response = $results->getExtraData('elasticsearch_response');
      $aggs = $query->getOption('aggs_geohash_grid');

      $facet_results = $results->getExtraData('search_api_facets');

      // Create an empty array that will be attached to the result object.

      foreach ($aggs as $key => $facet) {
        $terms = [];

        $buckets = $response['aggregations'][$key]['buckets'];

        //set default value, never empty
        if(empty($buckets)){
          $terms[] = [
            'count' => 0,
            'filter' => 0,
          ];
        }

        array_walk($buckets, function ($value) use (&$terms) {
          $terms[] = [
            'count' => $value['doc_count'],
            'filter' => $value['key'],
          ];
        });
        $facet_results[$key] = $terms;
      }

      $results->setExtraData('search_api_facets', $facet_results);

    }
  }

}
