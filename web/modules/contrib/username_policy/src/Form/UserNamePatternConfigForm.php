<?php

namespace Drupal\username_policy\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class userNamePatternConfigForm.
 */
class UserNamePatternConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'username_policy.usernamepatternconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'username_pattern_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('username_policy.usernamepatternconfig');
    $form['username_policy'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('User Name Policy Configuration'),
    ];
    $form['username_policy']['username_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pattern for username'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('username_pattern'),
    ];
    $form['username_policy']['note'] = [
      '#markup' => $this->t('Note:
                    <ul>
                    <li>Use only the below pattern elements to set pattern.</li>
                    <li>Only mandatory fields from registration page are allowed as pattern elements.</li>
                    <li>If pattern is empty username will be your email id.</li>
                    </ul>
                    <br/>
                 '),
    ];
    $form['username_policy']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];
    $form['username_policy']['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Only use the below pattern elements.'),
    ];
    $form['username_policy']['token_tree'] = [
      '#prefix' => '<div id="username_policy_token_tree">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
      '#theme' => 'username_policy_pattern_elements',
    ];
    foreach ($this->username_policy_generate_pattern_fields() as $pattern_ele) {
      $form['username_policy']['token_tree'][] = [
        '#markup' => $pattern_ele,
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('username_policy.usernamepatternconfig')
      ->set('username_pattern', $form_state->getValue('username_pattern'))
      ->save();
  }

  /**
   * Returns the list of fields based on the selected profile.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Bundle id.
   *
   * @return array
   *   Options for profile fields.
   */
  public function get_profile_fields($entity_type, $bundle) {
    $fields = \Drupal::entityManager()->getFieldDefinitions($entity_type, $bundle);
    foreach ($fields as $field_name => $field_definition) {
      if (!empty($field_definition->getTargetBundle()) && $field_definition->get('required') == 1) {
        $listFields[$field_name]['type'] = $field_definition->getType();
        $listFields[$field_name]['label'] = $field_definition->getLabel();
      }
    }
    return $listFields;
  }

  /**
   * Generate pattern fields.
   */
  public function username_policy_generate_pattern_fields() {
    // Prepare pattern elements from user main profile.
    $_fields = [];
    $user_req_fields = $this->get_profile_fields('user', 'user');
    if (!empty($user_req_fields)) {
      foreach ($user_req_fields as $field) {
        $_fields[] = $field['label'];
      }
    }
    // Prepare pattern elements from user other profile.
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('profile')) {
      $entity_type = 'profile_type';
      $entities = \Drupal::entityTypeManager()->getStorage($entity_type)->loadMultiple();
      $profile_req_fields = [];
      foreach ($entities as $entity) {
        $profile_allowed = \Drupal::entityTypeManager()->getStorage('profile_type')->load($entity->get('id'));
        if ($profile_allowed->getRegistration()) {
          $profile_req_fields[] = $this->get_profile_fields('profile', $entity->get('id'));
        }
      }
      if (!empty($profile_req_fields)) {
        foreach ($profile_req_fields as $fields) {
          foreach ($fields as $id => $field) {
            $_fields[] = '[' . $id . ']  -  ' . $field['label'] . '<br>';
          }
        }
      }
    }
    return $_fields;
  }

}
