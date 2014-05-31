<?php

/**
 * @file
 * Contains \Drupal\simple_crm\ContactListBuilder.
 */

namespace Drupal\simple_crm;

use Drupal\Component\Utility\String;
use Drupal\Core\Datetime\Date;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\Language;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of node entities.
 *
 * @see \Drupal\simple_crm\Entity\Contact
 */
class ContactListBuilder extends EntityListBuilder {

  /**
   * The date service.
   *
   * @var \Drupal\Core\Datetime\Date
   */
  protected $dateService;

  /**
   * Constructs a new ContactListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\Date $date_service
   *   The date service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, Date $date_service) {
    parent::__construct($entity_type, $storage);

    $this->dateService = $date_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('date')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // Enable language column and filter if multiple languages are enabled.
    $header = array(
      'sort_name' => $this->t('Sort Name'),
      'type' => array(
        'data' => $this->t('Contact type'),
        'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
      ),
      'changed' => array(
        'data' => $this->t('Updated'),
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
    );
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\simple_crm\ContactInterface $entity */
    $uri = $entity->urlInfo();
    $options = $uri->getOptions();
    $uri->setOptions($options);
    $row['title']['data'] = array(
      '#type' => 'link',
      '#title' => $entity->label(),
      //'#suffix' => ' ' . drupal_render($mark),
    ) + $uri->toRenderArray();
    $row['type'] = String::checkPlain(node_get_type_label($entity));
    $row['changed'] = $this->dateService->format($entity->getChangedTime(), 'short');
    return $row + parent::buildRow($entity);
  }

}
