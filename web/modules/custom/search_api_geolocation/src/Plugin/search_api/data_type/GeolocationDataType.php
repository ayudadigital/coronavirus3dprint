<?php

namespace Drupal\search_api_geolocation\Plugin\search_api\data_type;

use Drupal\search_api\DataType\DataTypePluginBase;

/**
 * Provides the geopoint data type.
 *
 * @SearchApiDataType(
 *   id = "geo_point",
 *   label = @Translation("Geolocation field Latitude/Longitude"),
 *   description = @Translation("Geopoint data type implementation"),
 *   default = "true"
 * )
 */
class GeolocationDataType extends DataTypePluginBase {

}
