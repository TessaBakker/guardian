<?php

namespace Drupal\guardian;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;

/**
 * Class GuardianManager.
 *
 * @package Drupal\guardian
 */
final class GuardianManager implements GuardianManagerInterface {

  use StringTranslationTrait, LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public function notifyModuleState($isEnabled) {
    $site = \Drupal::config('system.site')->get('name');

    if ($isEnabled) {
      $subject = $this->t('Guardian has been enabled for @site', [
        '@site' => $site,
      ]);
    }
    else {
      $subject = $this->t('Guardian has been disabled for @site', [
        '@site' => $site,
      ]);
    }

    $body = [$subject];

    $this->addMetadataToBody($body);

    $params = [
      'body' => $body,
      'subject' => $subject,
    ];

    $guardian_mail = Settings::get('guardian_mail');
    $user = \Drupal::entityTypeManager()->getStorage('user')->load(1);

    /** @var \Drupal\Core\Mail\MailManagerInterface $mailManager */
    $mailManager = \Drupal::service('plugin.manager.mail');
    $mailManager->mail('guardian', 'notification', $guardian_mail, $user->getPreferredLangcode(), $params, NULL, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultUserValues(UserInterface $user) {
    $guarded_users = $this->getGuardedUsers();

    if (isset($guarded_users[$user->id()])) {
      $user->get('init')->setValue($guarded_users[$user->id()]);
      $user
        ->setEmail($guarded_users[$user->id()])
        ->setPassword(NULL);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addMetadataToBody(array &$body) {
    $body[] = $this->t('Client IP: @ip', [
      '@ip' => \Drupal::request()->getClientIp(),
    ]);
    $body[] = $this->t('Host name: @host', [
      '@host' => \Drupal::request()->getHost(),
    ]);

    if (PHP_SAPI === 'cli') {
      $body[] = $this->t('Terminal user: @user', ['@user' => $_SERVER['USER'] ?: $this->t('Unknown')]);
    }

    \Drupal::moduleHandler()->alter('guardian_add_metadata_to_body', $body);
  }

  /**
   * {@inheritdoc}
   */
  public function destroySession(AccountInterface $account) {
    $current_user = \Drupal::currentUser();

    \Drupal::service('session_manager')->delete($account->id());

    if ($account->id() == $current_user->id()) {
      user_logout();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function showLogoutMessage() {
    $hours = Settings::get('guardian_hours', 2);
    $message = $this->formatPlural($hours,
      'Your last access was more than 1 hour ago, please login again.',
      'Your last access was more than @count hours ago, please login again.', ['@count' => $hours]);
    drupal_set_message($message, 'warning', TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function hasValidData(AccountInterface $account) {
    /** @var UserInterface $user */
    $user = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load($account->id());

    if ($user && is_null($user->getPassword())) {
      if ($user->getEmail() == $user->getInitialEmail()) {
        $guarded_users = $this->getGuardedUsers();

        if ($user->getEmail() == $guarded_users[$user->id()]) {
          return TRUE;
        }
      }
    }

    $this->getLogger('guardian')
      ->alert('User name <em>@username (id:@uid, mail:@mail, init:@init) has a changed password or e-mail address</em>', [
        '@username' => $user->getAccountName(),
        '@uid' => $user->id(),
        '@mail' => $user->getEmail(),
        '@init' => $user->getInitialEmail(),
      ]);

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasValidSession(AccountInterface $account) {
    $guardian_seconds = 3600 * Settings::get('guardian_hours', 2);
    return $account->getLastAccessedTime() > (\Drupal::time()
          ->getRequestTime() - $guardian_seconds);
  }

  /**
   * {@inheritdoc}
   */
  public function isGuarded(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return FALSE;
    }

    $guarded_users = $this->getGuardedUsers();

    return isset($guarded_users[$account->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getGuardedUids() {
    return array_keys($this->getGuardedUsers());
  }

  /**
   * {@inheritdoc}
   */
  private function getGuardedUsers() {
    static $users = [];

    if (empty($users)) {
      $mail_validator = \Drupal::service('email.validator');
      $implementations = \Drupal::moduleHandler()
        ->getImplementations('guardian_guarded_users');

      foreach ($implementations as $module) {
        $function = $module . '_guardian_guarded_users';
        $guarded_users = $function();
        foreach ($guarded_users as $uid => $mail) {
          if (empty($mail) || !is_int($uid) || $uid < 2 || !$mail_validator->isValid($mail)) {
            unset($guarded_users[$uid]);
          }
        }

        $users += $guarded_users;
      }

      $users[1] = Settings::get('guardian_mail');
    }

    return $users;
  }

}
