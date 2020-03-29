<?php

namespace Drupal\minifyhtml\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for the module.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'minifyhtml.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['minifyhtml_minify'] = [
      '#type'          => 'checkbox',
      '#title'         => t('Minified Source HTML.'),
      '#description'   => t('Toggle minified HTML on or off.'),
      '#default_value' => $this->config('minifyhtml.config')->get('minify'),
    ];

    $form['strip_comments'] = [
      '#title'         => $this->t('Strip comments from the source HTML'),
      '#description'   => $this->t('If checked, strip HTML comments and multi-line comments in @script and @style tags.', ['@script' => '<script>', '@style' => '<style>']),
      '#type'          => 'checkbox',
      '#default_value' => $this->config('minifyhtml.config')->get('strip_comments'),
    ];

    $form['save'] = [
      '#type'          => 'submit',
      '#value'         => $this->t('Save settings'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'minifyhtml_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('minifyhtml.config')
      ->set('strip_comments', $form_state->getValue('strip_comments'))
      ->set('minify', (bool) $form_state->getValue('minifyhtml_minify'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
