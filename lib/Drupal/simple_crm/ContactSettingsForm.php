<?php

/**
 * @file
 * Contains \Drupal\user\ContactSettingsForm.
 */

namespace Drupal\simple_crm;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure user settings for this site.
 */
class ContactSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\user\ContactSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandler $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_crm_admin_settings';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state) {
    $config = $this->config('simple_crm.settings');

    // Settings for anonymous users.
    $form['general_settings'] = array(
      '#type' => 'details',
      '#title' => $this->t('General Settings'),
      '#open' => TRUE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    parent::submitForm($form, $form_state);

    $this->config('user.settings')
      ->set('anonymous', $form_state['values']['anonymous'])
      ->set('admin_role', $form_state['values']['user_admin_role'])
      ->set('register', $form_state['values']['user_register'])
      ->set('password_strength', $form_state['values']['user_password_strength'])
      ->set('verify_mail', $form_state['values']['user_email_verification'])
      ->set('signatures', $form_state['values']['user_signatures'])
      ->set('cancel_method', $form_state['values']['user_cancel_method'])
      ->set('notify.status_activated', $form_state['values']['user_mail_status_activated_notify'])
      ->set('notify.status_blocked', $form_state['values']['user_mail_status_blocked_notify'])
      ->set('notify.status_canceled', $form_state['values']['user_mail_status_canceled_notify'])
      ->save();
    $this->config('user.mail')
      ->set('cancel_confirm.body', $form_state['values']['user_mail_cancel_confirm_body'])
      ->set('cancel_confirm.subject', $form_state['values']['user_mail_cancel_confirm_subject'])
      ->set('password_reset.body', $form_state['values']['user_mail_password_reset_body'])
      ->set('password_reset.subject', $form_state['values']['user_mail_password_reset_subject'])
      ->set('register_admin_created.body', $form_state['values']['user_mail_register_admin_created_body'])
      ->set('register_admin_created.subject', $form_state['values']['user_mail_register_admin_created_subject'])
      ->set('register_no_approval_required.body', $form_state['values']['user_mail_register_no_approval_required_body'])
      ->set('register_no_approval_required.subject', $form_state['values']['user_mail_register_no_approval_required_subject'])
      ->set('register_pending_approval.body', $form_state['values']['user_mail_register_pending_approval_body'])
      ->set('register_pending_approval.subject', $form_state['values']['user_mail_register_pending_approval_subject'])
      ->set('status_activated.body', $form_state['values']['user_mail_status_activated_body'])
      ->set('status_activated.subject', $form_state['values']['user_mail_status_activated_subject'])
      ->set('status_blocked.body', $form_state['values']['user_mail_status_blocked_body'])
      ->set('status_blocked.subject', $form_state['values']['user_mail_status_blocked_subject'])
      ->set('status_canceled.body', $form_state['values']['user_mail_status_canceled_body'])
      ->set('status_canceled.subject', $form_state['values']['user_mail_status_canceled_subject'])
      ->save();
    $this->config('system.site')
      ->set('mail_notification', $form_state['values']['mail_notification_address'])
      ->save();
  }

}
