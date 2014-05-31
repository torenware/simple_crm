<?php

/**
 * @file
 * Definition of Drupal\simple_crm\ContactForm.
 */

namespace Drupal\simple_crm;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Component\Utility\String;

/**
 * Form controller for the node edit forms.
 */
class ContactForm extends ContentEntityForm {

  /**
   * Default settings for this content/node type.
   *
   * @var array
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    /** @var \Drupal\simple_crm\ContactInterface $contact */
    $contact = $this->entity;
    // Set up default values, if required.
    //$this->settings = $type->getModuleSettings('simple_crm');

    // If this is a new node, fill in the default values.
    if ($contact->isNew()) {
      //none yet
    }
    else {
      $contact->date = format_date($contact->getCreatedTime(), 'custom', 'Y-m-d H:i:s O');
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   */
  public function form(array $form, array &$form_state) {
    /** @var \Drupal\simple_crm\ContactInterface $contact */
    $contact = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit Contact</em> @display_name', array('@display_name' => $contact->label()));
    }

    $user_config = \Drupal::config('user.settings');

    // Override the default CSS class name, since the user-defined node type
    // name in 'TYPE-node-form' potentially clashes with third-party class
    // names.
    $form['#attributes']['class'][0] = drupal_html_class('simple-crm-contact-form');

    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = array(
      '#type' => 'hidden',
      '#default_value' => $contact->getChangedTime(),
    );

    $form['created'] = array(
      '#type' => 'textfield',
      '#title' => t('Authored on'),
      '#maxlength' => 25,
      '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($contact->date) ? date_format(date_create($contact->date), 'Y-m-d H:i:s O') : format_date($contact->getCreatedTime(), 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($contact->date) ? date_format(date_create($contact->date), 'O') : format_date($contact->getCreatedTime(), 'custom', 'O'))),
      '#default_value' => !empty($contact->date) ? $contact->date : '',
      '#group' => 'author',
      '#access' => user_access('administer contacts'),
    );

    return parent::form($form, $form_state, $contact);
  }

  /**
   * Updates the node object by processing the submitted values.
   *
   * This function can be called by a "Next" button of a wizard to update the
   * form state's entity with the current step's values before proceeding to the
   * next step.
   *
   * Overrides Drupal\Core\Entity\EntityForm::submit().
   */
  public function submit(array $form, array &$form_state) {
    // Build the node object from the submitted values.
    $contact = parent::submit($form, $form_state);

    $contact->validated = TRUE;
    foreach (\Drupal::moduleHandler()->getImplementations('simple_crm_contact_submit') as $module) {
      $function = $module . '_simple_crm_contact_submit';
      $function($contact, $form, $form_state);
    }

    return $contact;
  }


  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    /** @var \Drupal\simple_crm\ContactInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    if (!empty($form_state['values']['created']) && $form_state['values']['created'] instanceOf DrupalDateTime) {
      $entity->setCreatedTime($form_state['values']['created']->getTimestamp());
    }
    else {
      $entity->setCreatedTime(REQUEST_TIME);
    }
    return $entity;
  }


  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, array &$form_state) {
    $contact = $this->entity;
    $insert = $contact->isNew();
    $contact->save();
    $contact_link = l(t('View'), 'contact/' . $contact->id());
    $watchdog_args = array('@type' => 'simple_crm_contact', '%title' => $contact->label());
    $t_args = array('@type' => 'Contact', '%title' => $contact->label());

    if ($insert) {
      watchdog('content', '@type: added %title.', $watchdog_args, WATCHDOG_NOTICE, $contact_link);
      drupal_set_message(t('@type %title has been created.', $t_args));
    }
    else {
      watchdog('content', '@type: updated %title.', $watchdog_args, WATCHDOG_NOTICE, $contact_link);
      drupal_set_message(t('@type %title has been updated.', $t_args));
    }

    if ($contact->id()) {
      $form_state['values']['nid'] = $contact->id();
      $form_state['nid'] = $contact->id();
      if ($contact->access('view')) {
        $form_state['redirect_route'] = array(
          'route_name' => 'simple_crm.contact.view',
          'route_parameters' => array(
            'simple_crm_contact' => $contact->id(),
          ),
        );
      }
      else {
        $form_state['redirect_route']['route_name'] = '<front>';
      }
    }
    else {
      // In the unlikely case something went wrong on save, the node will be
      // rebuilt and node form redisplayed the same way as in preview.
      drupal_set_message(t('The contact could not be saved.'), 'error');
      $form_state['rebuild'] = TRUE;
    }

    // Clear the page and block caches.
    Cache::invalidateTags(array('content' => TRUE));
  }

}
