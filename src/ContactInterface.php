<?php

/**
 * @file
 *   Contains
 */

namespace Drupal\simple_crm;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a contact entity.
 */
interface ContactInterface extends ContentEntityInterface, EntityChangedInterface {


  /**
   * Returns the contact display name.
   *
   * @return string
   *   Display name of the contact.
   */
  public function getDisplayName();

  /**
   * Sets the contact display name.
   *
   * @param string $display_name
   *   The node title.
   *
   * @return \Drupal\simple_crm\ContactInterface
   *   The called contact entity.
   */
  public function setDisplayName($display_name);

  /**
   * Returns the contact sort name.
   *
   * @return string
   *   Display sortable name of the contact.
   */
  public function getSortName();

  /**
   * Sets the contact display name.
   *
   * @param string $sort_name
   *   The contact sortable name.
   *
   * @return \Drupal\simple_crm\ContactInterface
   *   The called contact entity.
   */
  public function setSortName($sort_name);

  /**
   * Returns the contact creation timestamp.
   *
   * @return int
   *   Creation timestamp of the contact.
   */
  public function getCreatedTime();

  /**
   * Sets the contact creation timestamp.
   *
   * @param int $timestamp
   *   The node creation timestamp.
   *
   * @return \Drupal\simple_crm\ContactInterface
   *   The called contact entity.
   */
  public function setCreatedTime($timestamp);


}
