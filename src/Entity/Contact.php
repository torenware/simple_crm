<?php

/**
 * @file
 * Contains \Drupal\simple_crm\Entity\Contact.
 */

namespace Drupal\simple_crm\Entity;

use Drupal\simple_crm\ContactInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the contact entity class.
 *
 * @ContentEntityType(
 *   id = "simple_crm_contact",
 *   label = @Translation("Contact"),
 *   bundle_label = @Translation("Contact type"),
 *   admin_permission = "administer contacts",
 *   controllers = {
 *     "view_builder" = "Drupal\simple_crm\ContactViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\simple_crm\ContactForm",
 *       "delete" = "Drupal\simple_crm\Form\ContactDeleteForm",
 *       "edit" = "Drupal\simple_crm\ContactForm"
 *     },
 *     "list_builder" = "Drupal\simple_crm\ContactListBuilder",
 *   },
 *   base_table = "simple_crm_contact",
 *   uri_callback = "simple_crm_contact_uri",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "scid",
 *     "label" = "sort_name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "simple_crm.contact.view",
 *     "admin-form" = "simple_crm_contact.account_settings",
 *     "delete-form" = "simple_crm.contact.delete_confirm",
 *     "edit-form" = "simple_crm.contact.edit"
 *   }
 * )
 */
class Contact extends ContentEntityBase implements ContactInterface {

  const DEFAULT_CONTACT_TYPE = "simple_crm_contact";
  
  /**
   * Constructor for a contact
   */
  public function __construct(array $values) {
    parent::__construct($values, self::DEFAULT_CONTACT_TYPE);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    $disp = $this->display_name->value;
    if (empty($disp)) {
      //TODO -- add to the interface so we can override
      $first = $this->first_name->value;
      $last = $this->last_name->value;
      $this->display_name->value = "$first $last";
    }
    if (empty($this->sort_name->value)) {
      $this->sort_name->value = "{$this->last_name->value}, {$this->first_name->value}";
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $nodes) {
    parent::postDelete($storage, $nodes);
    //\Drupal::service('node.grant_storage')->deleteContactRecords(array_keys($nodes));
  }


  /**
   * {@inheritdoc}
   */
  public function access($operation = 'view', AccountInterface $account = NULL) {
    if ($operation == 'create') {
      return parent::access($operation, $account);
    }

    return \Drupal::entityManager()
      ->getAccessController($this->entityTypeId)
      ->access($this, $operation, NULL, $account);
  }


  /**
   * {@inheritdoc}
   */
  public function getDisplayName() {
    return $this->get('display_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplayName($display_name) {
    $this->set('display_name', $display_name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSortName() {
    return $this->get('sort_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSortName($sort_name) {
    $this->set('sort_name', $sort_name);
    return $this;
  }




  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }


  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['scid'] = FieldDefinition::create('integer')
      ->setLabel(t('Contact ID'))
      ->setDescription(t('The contact ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The contact UUID.'))
      ->setReadOnly(TRUE);


    $fields['display_name'] = FieldDefinition::create('string')
      ->setLabel(t('Display Name'))
      ->setDescription(t('The display name this node, always treated as non-markup plain text.'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 128,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['sort_name'] = FieldDefinition::create('string')
      ->setLabel(t('Sort Name'))
      ->setDescription(t('The sortable name this contact, always treated as non-markup plain text.'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 128,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ));


    $fields['created'] = FieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the contact was created.'));

    $fields['changed'] = FieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the contact was last edited.'));
    
    $fields['first_name'] = FieldDefinition::create('string')
      ->setLabel(t('First Name'))
      ->setDescription(t('First name of a contact'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 128,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'string',
        'weight' => -7,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['last_name'] = FieldDefinition::create('string')
      ->setLabel(t('Last Name'))
      ->setDescription(t('Last name of a contact'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 128,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'string',
        'weight' => -7,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }


}
