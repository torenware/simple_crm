<?php

/**
 * @file
 * Definition of Drupal\simple_crm\ContactViewBuilder.
 */

namespace Drupal\simple_crm;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for simple_crms.
 */
class ContactViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode, $langcode = NULL) {
    /** @var \Drupal\simple_crm\ContactInterface[] $entities */
    if (empty($entities)) {
      return;
    }

    parent::buildComponents($build, $entities, $displays, $view_mode, $langcode);

    foreach ($entities as $id => $entity) {
      $bundle = $entity->bundle();
      $display = $displays[$bundle];

      $callback = '\Drupal\simple_crm\ContactViewBuilder::renderLinks';
      $context = array(
        'simple_crm_contact_entity_id' => $entity->id(),
        'view_mode' => $view_mode,
        'langcode' => $langcode,
        'in_preview' => !empty($entity->in_preview),
        'token' => drupal_render_cache_generate_token(),
      );

      $build[$id]['links'] = array(
        '#post_render_cache' => array(
          $callback => array(
            $context,
          ),
        ),
        '#markup' => drupal_render_cache_generate_placeholder($callback, $context, $context['token']),
      );


      // Add Language field text element to simple_crm_contact render array.
      if ($display->getComponent('langcode')) {
        $build[$id]['langcode'] = array(
          '#type' => 'item',
          '#title' => t('Language'),
          '#markup' => $entity->language()->name,
          '#prefix' => '<div id="field-language-display">',
          '#suffix' => '</div>'
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode, $langcode) {
    $defaults = parent::getBuildDefaults($entity, $view_mode, $langcode);

    // Don't cache simple_crm_contacts that are in 'preview' mode.
    if (isset($defaults['#cache']) && isset($entity->in_preview)) {
      unset($defaults['#cache']);
    }
    else {
      // The simple_crm_contact 'submitted' info is not rendered in a standard way (renderable
      // array) so we have to add a cache tag manually.
      // @todo Delete this once https://drupal.org/simple_crm/2226493 lands.
      $defaults['#cache']['tags']['user'][] = $entity->getOwnerId();
    }

    return $defaults;
  }

  /**
   * #post_render_cache callback; replaces the placeholder with simple_crm_contact links.
   *
   * Renders the links on a simple_crm_contact.
   *
   * @param array $element
   *   The renderable array that contains the to be replaced placeholder.
   * @param array $context
   *   An array with the following keys:
   *   - simple_crm_contact_entity_id: a simple_crm_contact entity ID
   *   - view_mode: the view mode in which the simple_crm_contact entity is being viewed
   *   - langcode: in which language the simple_crm_contact entity is being viewed
   *   - in_preview: whether the simple_crm_contact is currently being previewed
   *
   * @return array
   *   A renderable array representing the simple_crm_contact links.
   */
  public static function renderLinks(array $element, array $context) {
    $callback = '\Drupal\simple_crm\ContactViewBuilder::renderLinks';
    $placeholder = drupal_render_cache_generate_placeholder($callback, $context, $context['token']);

    $links = array(
      '#theme' => 'links__simple_crm_contact',
      '#pre_render' => array('drupal_pre_render_links'),
      '#attributes' => array('class' => array('links', 'inline')),
    );

    if (!$context['in_preview']) {
      $entity = entity_load('simple_crm_contact', $context['simple_crm_contact_entity_id'])->getTranslation($context['langcode']);
      $links['simple_crm_contact'] = self::buildLinks($entity, $context['view_mode']);

      // Allow other modules to alter the simple_crm_contact links.
      $hook_context = array(
        'view_mode' => $context['view_mode'],
        'langcode' => $context['langcode'],
      );
      \Drupal::moduleHandler()->alter('simple_crm_contact_links', $links, $entity, $hook_context);
    }
    $markup = drupal_render($links);
    $element['#markup'] = str_replace($placeholder, $markup, $element['#markup']);

    return $element;
  }

  /**
   * Build the default links (Read more) for a simple_crm_contact.
   *
   * @param \Drupal\simple_crm\ContactInterface $entity
   *   The simple_crm_contact object.
   * @param string $view_mode
   *   A view mode identifier.
   *
   * @return array
   *   An array that can be processed by drupal_pre_render_links().
   */
  protected static function buildLinks(ContactInterface $entity, $view_mode) {
    $links = array();

    // Always display a read more link on teasers because we have no way
    // to know when a teaser view is different than a full view.
    if ($view_mode == 'teaser') {
      $simple_crm_contact_title_stripped = strip_tags($entity->label());
      $links['simple_crm_contact-readmore'] = array(
        'title' => t('Read more<span class="visually-hidden"> about @title</span>', array(
          '@title' => $simple_crm_contact_title_stripped,
        )),
        'href' => 'simple_crm_contact/' . $entity->id(),
        'language' => $entity->language(),
        'html' => TRUE,
        'attributes' => array(
          'rel' => 'tag',
          'title' => $simple_crm_contact_title_stripped,
        ),
      );
    }

    return array(
      '#theme' => 'links__simple_crm_contact__simple_crm',
      '#links' => $links,
      '#attributes' => array('class' => array('links', 'inline')),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode, $langcode = NULL) {
    /** @var \Drupal\simple_crm\ContactInterface $entity */
    parent::alterBuild($build, $entity, $display, $view_mode, $langcode);
    if ($entity->id()) {
      $build['#contextual_links']['simple_crm_contact'] = array(
        'route_parameters' =>array('simple_crm_contact' => $entity->id()),
        'metadata' => array('changed' => $entity->getChangedTime()),
      );
    }
  }

}
