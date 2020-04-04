<?php

namespace Drupal\x_frame_options_configuration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;

/**
 * Configure example settings for this site.
 */
class XFrameOptionsConfigurationSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'x_frame_options_configuration_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'x_frame_options_configuration.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('x_frame_options_configuration.settings');

    // Markup to explain what the X-Frame-Options HTTP response header is.
    $form['markup'] = [
      '#type' => 'markup',
      '#markup' => $this
        ->t('<h3>Description:</h3><p>The X-Frame-Options HTTP response header can be used to indicate whether or not a browser should be allowed to render a page in a &lt;frame&gt;, &lt;iframe&gt; or &lt;object&gt;. Sites can use this to avoid clickjacking attacks, by ensuring that their content is not embedded into other sites.</p>'),
    ];

    /*
     * Create the field that will allow users to select which directive to use
     *   ('DENY', 'SAMEORIGIN', 'ALLOW-FROM').
     */
    $form['directive'] = [
      '#type' => 'radios',
      '#title' => $this->t('Directive'),
      '#default_value' => $config
        ->get('x_frame_options_configuration.directive', 'DENY'),
      '#options' => [
        'DENY' => $this->t('DENY'),
        'SAMEORIGIN' => $this->t('SAMEORIGIN'),
        'ALLOW-FROM' => $this->t('ALLOW-FROM uri'),
      ],
      '#required' => TRUE,
    ];

    /*
     * Create the field that will allow the users to specify the URI that will
     * be allowed to render this page. This field will only be visible when the
     * directive field has the 'ALLOW-FROM' option checked.
     */
    $form['allow-from-uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Uri (if "ALLOW-FROM uri" is selected).'),
      '#default_value' => $config
        ->get('x_frame_options_configuration.allow-from-uri'),
      '#placeholder' => $this->t('http://domain.com/'),
      '#description' => $this
        ->t('<strong>Use with caution because this directive might be ignored on Google Chrome or Safari and your site will allow to be rendered from anywhere</strong>.<br />Check for detailed <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options#Browser_compatibility" target="_blank">browser compatibility</a> information.'),
      '#states' => [
        'visible' => [
          ':input[name="directive"]' => ['value' => 'ALLOW-FROM'],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable('x_frame_options_configuration.settings')
      // Set the submitted configuration setting.
      ->set('x_frame_options_configuration.directive', Html::escape($form_state->getValue('directive')))
      ->set('x_frame_options_configuration.allow-from-uri', UrlHelper::stripDangerousProtocols(Html::escape($form_state->getValue('allow-from-uri'))))
      // Save the config values.
      ->save();

    parent::submitForm($form, $form_state);
  }

}
