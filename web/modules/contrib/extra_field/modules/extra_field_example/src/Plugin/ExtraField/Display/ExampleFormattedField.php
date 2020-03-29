<?php

namespace Drupal\extra_field_example\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Example Extra field with formatted output.
 *
 * @ExtraFieldDisplay(
 *   id = "formatted_field",
 *   label = @Translation("Data formatted as field with label"),
 *   bundles = {
 *     "node.article",
 *   }
 * )
 */
class ExampleFormattedField extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Three items');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'above';
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    return [
      ['#markup' => 'One'],
      ['#markup' => 'Two'],
      ['#markup' => 'Three'],
    ];
  }

}
