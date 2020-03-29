<?php

namespace Drupal\extra_field_example\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Example Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "multilingual_field",
 *   label = @Translation("Concatenated tags"),
 *   bundles = {
 *     "node.article"
 *   }
 * )
 */
class ExampleMultilingualField extends ExtraFieldDisplayFormattedBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {

    $elements = [];
    $cache = new CacheableMetadata();
    $tagsField = $this->getTagsField();

    if ($tagsField && !$tagsField->isEmpty()) {

      // Build the field output as a concatenated string of tags.
      $tags = [];
      foreach ($tagsField as $item) {
        /** @var \Drupal\Core\Entity\ContentEntityInterface $tag */
        $tag = $item->entity;
        $tags[] = $tag->label();
        $cache->addCacheableDependency($tag);
      }
      $elements = ['#markup' => implode(', ', $tags)];
    }
    else {
      // Mark the result as empty to make sure no field wrapper is applied.
      $this->isEmpty = TRUE;
    }

    $cache->applyTo($elements);
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {

    $label = '';
    if ($tagsField = $this->getTagsField()) {
      // Use the Tags field's label.
      $label = $tagsField->getFieldDefinition()->getLabel();
    }

    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    // Override the default label display setting.
    return 'inline';
  }

  /**
   * {@inheritdoc}
   */
  public function isTranslatable() {
    // Override the default translatability setting.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode() {

    if ($tagsField = $this->getTagsField()) {
      $langcode = $tagsField->getLangcode();
    }
    else {
      $langcode = parent::getLangcode();
    }

    return $langcode;
  }

  /**
   * Returns the Tags field this plugin uses.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   *   The taxonomy terms this field references.
   */
  protected function getTagsField() {
    $field = NULL;
    $entity = $this->getEntity();

    if ($entity->hasField('field_tags')) {
      $field = $entity->get('field_tags');
    }
    return $field;
  }

}
