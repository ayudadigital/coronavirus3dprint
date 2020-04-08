<?php

namespace Drupal\fixed_text_link_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'file_url_plain' formatter.
 *
 * @FieldFormatter(
 *   id = "fixed_text_file_url",
 *   label = @Translation("Link with a fixed text"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class FixedTextFileUrl extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'link_text' => 'Download',
        'link_class' => '',
        'open_in_new_window' => FALSE,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $parentForm, FormStateInterface $form_state) {
    $parentForm = parent::settingsForm($parentForm, $form_state);

    $form['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $this->getLinkText(),
      '#required' => TRUE,
    ];

    $form['link_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link class'),
      '#default_value' => $this->getLinkClass(),
      '#required' => FALSE,
    ];

    $form['open_in_new_window'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open in a new window'),
      '#default_value' => $this->openLinkInNewWindow(),
    ];

    return $form + $parentForm;
  }

  /**
   * @return string
   */
  private function getLinkText() {
    return $this->getSettings()['link_text'];
  }

  /**
   * @return string
   */
  private function getLinkClass() {
    return $this->getSettings()['link_class'];
  }

  /**
   * @return boolean
   */
  private function openLinkInNewWindow() {
    return $this->getSettings()['open_in_new_window'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Link text: @text', ['@text' => $this->getTranslatedLinkText()]);
    if (!empty($settings['link_class'])) {
      $summary[] = $this->t('Link class: @text', ['@text' => $this->getLinkClass()]);
    }

    return $summary;
  }

  /**
   * @return string
   */
  private function getTranslatedLinkText() {
    return $this->t($this->getLinkText(), [], ['context' => 'User entered link title']);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\file\Entity\File $file */
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $text = $this->getTranslatedLinkText();
      $url = Url::fromUri(file_create_url($file->getFileUri()));
      $link = Link::fromTextAndUrl($text, $url);

      $build = $link->toRenderable();
      $build['#attributes']['class'][] = $this->getLinkClass();
      if ($this->openLinkInNewWindow()) {
        $build['#attributes']['target'] = '_blank';
      }
      $cacheableMetadata = CacheableMetadata::createFromObject($file);
      $cacheableMetadata->applyTo($build);
      $elements[$delta] = $build;
    }

    return $elements;
  }

}
