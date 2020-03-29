<?php

namespace Drupal\extra_field\Plugin;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;

/**
 * Base class for Extra field Display plugins with field wrapper output.
 */
abstract class ExtraFieldDisplayFormattedBase extends ExtraFieldDisplayBase implements ExtraFieldDisplayFormattedInterface {

  /**
   * Flag to indicate that the extra field has no content.
   *
   * Set this flag when the render elements returned by ::viewElements only
   * contains non-visible render data such as #cache or #attached but does not
   * contain actual renderable data such as #markup, #theme or #item.
   *
   * When this flag is set, the render elements will not be wrapped in a field
   * wrapper.
   *
   * @var bool
   */
  protected $isEmpty = FALSE;

  /**
   * The langcode of the field values.
   *
   * @var string
   */
  protected $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $elements = $this->viewElements($entity);

    if (!empty($elements) && !$this->isEmpty()) {
      // Construct a render array for the extra field elements.
      // @see \Drupal\Core\Field\FormatterBase::view
      $build = [
        '#theme' => 'field',
        '#title' => $this->getLabel(),
        '#label_display' => $this->getLabelDisplay(),
        // Prevent quickedit from editing this field by using a special view
        // mode.
        // @see quickedit_preprocess_field()
        '#view_mode' => '_custom',
        '#language' => $this->getLangcode(),
        '#field_name' => $this->getFieldName(),
        '#field_type' => $this->getFieldType(),
        '#field_translatable' => $this->isTranslatable(),
        '#entity_type' => $entity->getEntityTypeId(),
        '#bundle' => $entity->bundle(),
        '#object' => $entity,
        '#formatter' => $this->getPluginId(),
      ];

      if ($children = Element::children($elements, TRUE)) {
        $build['#is_multiple'] = TRUE;
        $build['#cache'] = !empty($elements['#cache']) ? $elements['#cache'] : [];

        // Without #children the field will not show up.
        $build['#children'] = '';

        foreach ($children as $key) {
          // Only keys in "#items" property are required in
          // template_preprocess_field().
          $build['#items'][$key] = new \stdClass();
          $build[$key] = $elements[$key];
        }
      }
      else {
        $build['#is_multiple'] = FALSE;
        // Only keys in "#items" property are required in
        // template_preprocess_field().
        $build['#items'][] = new \stdClass();
        $build[] = $elements;
      }
    }
    else {
      $build = $elements;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'hidden';
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldType() {
    return 'extra_field';
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldName() {
    return 'extra_field_' . $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return $this->isEmpty;
  }

  /**
   * {@inheritdoc}
   */
  public function setLangcode($langcode) {
    $this->langcode = $langcode;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode() {
    return $this->langcode;
  }

  /**
   * {@inheritdoc}
   */
  public function isTranslatable() {
    return FALSE;
  }

}
