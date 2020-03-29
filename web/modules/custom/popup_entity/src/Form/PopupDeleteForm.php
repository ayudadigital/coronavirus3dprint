<?php

namespace Drupal\popup_entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a popup_entity entity.
 *
 * @ingroup popup_entity
 */
class PopupDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete entity "%name"?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   *
   * If the delete command is canceled, return to the popup list.
   */
  public function getCancelUrl() {
    return new Url('entity.popup_entity_popup.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * Delete the entity and log the event. logger() replaces the watchdog.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->delete();

    $this->logger('popup_entity')->notice('@type: deleted %title.',
      [
        '@type' => $this->entity->bundle(),
        '%title' => $this->entity->label(),
      ]);
    $form_state->setRedirect('entity.popup_entity_popup.collection');
  }

}
