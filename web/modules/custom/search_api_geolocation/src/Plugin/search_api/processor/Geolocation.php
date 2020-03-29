<?php

namespace Drupal\search_api_geolocation\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the item's lat/long to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "search_api_geolocation",
 *   label = @Translation("Geolocation field"),
 *   description = @Translation("Adds lat/long to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 *
 * )
 */
class Geolocation extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Geolocation Lat/Long'),
        'description' => $this->t('A lat/long where the item can be accessed'),
        'type' => 'geo_point',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_geolocation'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();

    if ($entity->getEntityType()->id() == 'node') {
      $values = $entity->get('field_geolocation')->getValue();

      if(!empty($values[0])){
        $value = $values[0];
        $lat_lng = $value['lat'] . ',' . $value['lng'];
        $fields = $this->getFieldsHelper()->filterForPropertyPath(
          $item->getFields(),
          NULL,
          'search_api_geolocation'
        );
        foreach ($fields as $field) {
          $field->addValue($lat_lng);
        }
      }

    }

  }

}
