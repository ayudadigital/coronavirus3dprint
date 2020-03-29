<?php

namespace Drupal\popup_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the popup_entity entity edit forms.
 *
 * @ingroup popup_entity
 */
class PopupForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\popup_entity\Entity\Popup */
    $form = parent::buildForm($form, $form_state);

    $popup_form['breakpoints_group'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Breakpoints'),
      '#weight' => "0",
    );
    $popup_form['breakpoints_group']['breakpoints'] = $form['breakpoints'];
    unset($form['breakpoints']);

    $popup_form['display'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Display'),
      '#weight' => "1",
    );
    $popup_form['display']['width'] = $form['width'];
    $popup_form['display']['width']['widget'][0]['value']['#min'] = 0;
    unset($form['width']);
    $popup_form['display']['height'] = $form['height'];
    $popup_form['display']['height']['widget'][0]['value']['#min'] = 0;
    unset($form['height']);

    $popup_form['position'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Position'),
      '#weight' => "2",
    );
    $popup_form['position']['position_x'] = $form['position_x'];
    unset($form['position_x']);
    $popup_form['position']['position_y'] = $form['position_y'];
    unset($form['position_y']);

    $popup_form['actions'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Actions'),
      '#weight' => "3",
    );
    $popup_form['actions']['open_delay'] = $form['open_delay'];
    $popup_form['actions']['open_delay']['widget'][0]['value']['#min'] = 0;
    unset($form['open_delay']);

    $popup_form['actions']['times_to_show'] = $form['times_to_show'];
    $popup_form['actions']['times_to_show']['widget'][0]['value']['#min'] = 0;
    unset($form['times_to_show']);

    $popup_form['actions']['cookies_expiration'] = $form['cookies_expiration'];
    $popup_form['actions']['cookies_expiration']['#states'] = [
      'invisible' => array(
        ':input[name="times_to_show[0][value]"]' => array('value' => 0),
      ),
    ];
    $popup_form['actions']['cookies_expiration']['widget'][0]['value']['#min'] = 1;
    unset($form['cookies_expiration']);

    $form['advanced'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => "98",
    );

    $form['popup_settings'] = array(
      '#type' => 'details',
      '#title' => $this->t('Popup settings'),
      '#group' => 'advanced',
    );

    $form['author_settings'] = array(
      '#type' => 'details',
      '#title' => $this->t('Authoring information'),
      '#group' => 'advanced',
    );
    $form['author_settings']['user_id'] = $form['user_id'];
    unset($form['user_id']);

    $form['status_settings'] = array(
      '#type' => 'details',
      '#title' => $this->t('Status information'),
      '#group' => 'advanced',
    );
    $form['status_settings']['status'] = $form['status'];
    unset($form['status']);

    $form['popup_settings'] += $popup_form;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.popup_entity_popup.collection');
    $entity = $this->getEntity();
    $entity->save();
  }

}
