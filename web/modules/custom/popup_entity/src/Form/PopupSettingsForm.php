<?php

namespace Drupal\popup_entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PopuptEntitySettingsForm.
 *
 * @ingroup popup_entity
 */
class PopupSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'popup_entity_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    return $form;
  }

}
