<?php

namespace Drupal\fixed_text_link_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "fixed_text_link",
 *   label = @Translation("Link with fixed text"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class FixedTextLink extends LinkFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'trim_length' => '',
      'link_text' => 'View website',
      'link_class' => '',
      'allow_override' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $parentForm, FormStateInterface $form_state) {
    $parentForm = parent::settingsForm($parentForm, $form_state);

    unset($parentForm['trim_length']);
    unset($parentForm['url_only']);
    unset($parentForm['url_plain']);

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

    $form['allow_override'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Allow the title to be overridden"),
      '#default_value' => $this->getSetting('allow_override'),
    ];

    return $form + $parentForm;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();

    $summary[] = $this->t('Link text: @text', ['@text' => $this->getTranslatedLinkText()]);
    if (!empty($settings['link_class'])) {
      $summary[] = $this->t('Link class: @text', ['@text' => $this->getLinkClass()]);
    }
    if (!empty($settings['rel'])) {
      $summary[] = $this->t('Add rel="@rel"', ['@rel' => $settings['rel']]);
    }
    if (!empty($settings['target'])) {
      $summary[] = $this->t('Open link in new window');
    }

    if ($this->getSetting('allow_override')) {
      $summary[] = $this->t('Link text can be overridden');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $item */
    foreach ($items as $item) {
      $allowOverride = (bool) $this->getSetting('allow_override');
      $shouldOverride = $allowOverride && $item->get('title')->getString() === '';
      if (!$allowOverride || $shouldOverride) {
        $item->set('title', $this->getTranslatedLinkText());
      }
    }

    $elements = parent::viewElements($items, $langcode);

    foreach ($elements as &$element) {
      $element['#options']['attributes']['class'][] = $this->getLinkClass();
    }

    return $elements;
  }

  /**
   * @return mixed
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
   * @return mixed
   */
  private function getTranslatedLinkText() {
    return $this->t($this->getLinkText(), [], ['context' => 'User entered link title']);
  }
}
