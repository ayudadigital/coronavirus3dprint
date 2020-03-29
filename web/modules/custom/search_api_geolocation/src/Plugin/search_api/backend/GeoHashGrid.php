<?php

namespace Drupal\search_api_geolocation\Plugin\search_api\backend;

use nodespark\DESConnector\Elasticsearch\Aggregations\Bucket\Bucket;

/**
 * Class GeoHashGrid
 *
 * @package nodespark\DESConnector\Elasticsearch\Aggregations\Bucket
 */
class GeoHashGrid extends Bucket{

  const TYPE = 'geohash_grid';

  /**
   * @param string $aggrName
   * @param string $aggrFieldName
   * @param int $precision
   */
  public function __construct($aggrName, $aggrFieldName, $precision){
    parent::__construct($aggrName, $aggrFieldName);
    $this->addParameter('precision', $precision);
  }

}
