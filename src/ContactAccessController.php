<?php

/**
 * @file
 * Contains \Drupal\simple_crm\ContactAccessController.
 */

namespace Drupal\simple_crm;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityControllerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the access controller for the simple_crm entity type.
 */
class ContactAccessController extends EntityAccessController implements EntityControllerInterface {


  /**
   * Constructs a ContactAccessController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   */
  public function __construct(EntityTypeInterface $entity_type) {
    parent::__construct($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type
    );
  }


  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, $langcode = Language::LANGCODE_DEFAULT, AccountInterface $account = NULL) {
    if (!user_access('administer contacts', $account)) {
      return FALSE;
    }
    return parent::access($entity, $operation, $langcode, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function createAccess($entity_bundle = NULL, AccountInterface $account = NULL, array $context = array()) {
    $account = $this->prepareUser($account);

    if (user_access('administer contacts', $account)) {
      return TRUE;
    }

    return parent::createAccess($entity_bundle, $account, $context);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $node, $operation, $langcode, AccountInterface $account) {
    /** @var \Drupal\simple_crm\ContactInterface $node */
  }



}
