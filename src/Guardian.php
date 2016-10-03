<?php

namespace Drupal\guardian;

/**
 * Class Guardian
 * @package Drupal\guardian
 */
class Guardian {

  /**
   * @var \Drupal\Core\Entity\EntityInterface|null|static
   */
  public $account;

  /**
   * Guardian constructor.
   *
   * Loads the full user object of the current user.
   */
  function __construct() {
    $this->account = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
  }

  /**
   * Remove any guardian variable in the database.
   *
   * Every variable must be set within settings.php.
   */
  public function guardian_cleanup_database_variables() {
    \Drupal::configFactory()->getEditable('guardian.global.mail')->delete();
    \Drupal::configFactory()->getEditable('guardian.global.hours')->delete();
    drupal_static_reset();
  }

  /**
   * Send a status message to the Guardian mail address.
   */
  public function guardian_mail_status_update($status_text, $status_mail) {
    $site = \Drupal::config('system.site')->get('name');
    $subject = t('Guardian has been @status for @site', array(
      '@site' => $site,
      '@status' => $status_text,
    ));
    $body = array($subject);

    $this->guardian_add_metadata_to_body($body);

    $params = array(
      'body' => $body,
      'subject' => $subject,
    );

    $this->guardian_cleanup_database_variables();

    $guardian_mail = \Drupal::config('guadian.global')->get('mail');

    // Note: Drupal mail logs failed mailings in logs, no need to do this here.
    if ($guardian_mail) {
      $mailManager = \Drupal::service('plugin.manager.mail');
      $mailManager->mail('guardian', $status_mail, $guardian_mail, LANGCODE_NOT_SPECIFIED, $params, NULL, TRUE);
    }
  }

  /**
   * Destroy the session for user #1 and reset the account.
   *
   * @see guardian_cron().
   */
  public function guardian_reset_user1() {
    $guardian_mail = \Drupal::config('guardian.global')->get('mail');

    // Remove all active USER 1 sessions.
    $session_manager = \Drupal::service('session_manager');
    $session_manager->delete(1);

    $account = \Drupal\user\Entity\User::load(1);
    \Drupal::entityTypeManager()->getStorage('user')->resetCache(array(1));

    $account->init = $guardian_mail;
    $account->mail = $guardian_mail;
    $account->pass = '';

    // Update USER 1 with Guardian data.
    $account->save();
  }

  /**
   * Helper to add metadata within the body of an e-mail.
   *
   * @param array $body
   *   Message of mail.
   */
  public function guardian_add_metadata_to_body(&$body) {
    $body[] = t('Client IP: @ip', array('@ip' => \Drupal::request()->getClientIp()));
    $body[] = t('Hostname: @host', array('@host' => gethostname()));

    if (PHP_SAPI === 'cli') {
      $body[] = t('Terminal user: @user', array('@user' => $_SERVER['USER']));
    }

    \Drupal::moduleHandler()->alter('guardian_add_metadata_to_body', $body);
  }

  /**
   * Helper to check for valid Guardian user data.
   *
   * @return bool TRUE if user#1 has valid data.
   */
  public function guardian_check_valid_data() {
    $this->guardian_cleanup_database_variables();

    $guardian_mail = \Drupal::config('guardian.global')->get('mail');
    $has_init = $this->account->init->value == $guardian_mail;
    $has_mail = $this->account->mail->value == $guardian_mail;
    $has_empty_pass = empty($this->account->pass->value);

    $is_valid = $guardian_mail && $has_init && $has_mail && $has_empty_pass;

    if (!$is_valid) {
      \Drupal::logger('my_module')->alert('Guardian detected problems with USER 1 account: %user', array(
        '%user' => var_export($this->account, TRUE)));
      $this->guardian_reset_user1();
    }

    return $is_valid;
  }

  /**
   * Helper to check for valid Guardian user session lifetime.
   *
   * @return bool TRUE if last access was still in time limit.
   */
  public function guardian_check_valid_session() {
    $config = \Drupal::config('guardian.global');
    return $this->account->access->value > REQUEST_TIME - 3600 * $config->get('hours');
  }

  /**
   * Helper function to destroy all USER 1 sessions.
   */
  public function guardian_destroy_user1_sessions() {
    if ($this->guardian_account_is_root()) {
      drupal_set_message(t('Session too old, please login again.'), 'warning');
      user_logout();
    }
    else {
      $this->guardian_reset_user1();
    }
  }

  /**
   * Checks if account is USER 1.
   *
   * @return bool TRUE if user has uid 1.
   */
  public function guardian_account_is_root() {
    // Type casting, because uid is not always an integer and can't be a boolean.
    return (string) $this->account->id() === '1';
  }

}