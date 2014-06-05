<?php

/**
 * @file
 * Contains \Drupal\simple_crm\Form\ContactDeleteForm.
 */

namespace Drupal\simple_crm\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Provides a deletion confirmation form for Contact entity.
 */
class ContactDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the contact for %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return new Url('simple_crm.content_overview');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    watchdog('user', 'Contact for %name has been deleted.', array('%name' => $this->entity->label()));
    drupal_set_message($this->t('Contact for %name has been deleted.', array('%name' => $this->entity->label())));
    $form_state['redirect_route'] = $this->getCancelRoute();
  }

}
