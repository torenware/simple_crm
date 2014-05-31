<?php

/**
 * @file
 * Contains \Drupal\simple_crm\Controller\ContactController.
 */

namespace Drupal\simple_crm\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Drupal\simple_crm\ContactInterface;

/**
 * Returns responses for Node routes.
 */
class ContactController extends ControllerBase {


  /**
   * Provides the node submission form.
   *
   * @param \Drupal\simple_crm\NodeTypeInterface $node_type
   *   The node type entity for the node.
   *
   * @return array
   *   A node submission form.
   */
  public function add() {
    $account = $this->currentUser();

    $contact = $this->entityManager()->getStorage('simple_crm_contact')->create(array(
      'created' => time(),
    ));

    $form = $this->entityFormBuilder()->getForm($contact);

    return $form;
  }




  /**
   * Displays a contact.
   *
   * @param \Drupal\simple_crm\ContactInterface $node
   *   The node we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function page(ContactInterface $contact) {
    $build = $this->buildPage($contact);

    foreach ($contact->uriRelationships() as $rel) {
      // Set the node path as the canonical URL to prevent duplicate content.
      $build['#attached']['drupal_add_html_head_link'][] = array(
        array(
        'rel' => $rel,
        'href' => $contact->url($rel),
        )
        , TRUE);

      if ($rel == 'canonical') {
        // Set the non-aliased canonical path as a default shortlink.
        $build['#attached']['drupal_add_html_head_link'][] = array(
          array(
            'rel' => 'shortlink',
            'href' => $contact->url($rel, array('alias' => TRUE)),
          )
        , TRUE);
      }
    }

    return $build;
  }

  /**
   * The _title_callback for the node.view route.
   *
   * @param ContactInterface $contact
   *   The current node.
   *
   * @return string
   *   The page title.
   */
  public function pageTitle(ContactInterface $contact) {
    return String::checkPlain($this->entityManager()->getTranslationFromContext($contact)->label());
  }

  /**
   * Builds a node page render array.
   *
   * @param \Drupal\simple_crm\ContactInterface $contact
   *   The node we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  protected function buildPage(ContactInterface $contact) {
    return array('nodes' => $this->entityManager()->getViewBuilder('simple_crm_contact')->view($contact));
  }


}
