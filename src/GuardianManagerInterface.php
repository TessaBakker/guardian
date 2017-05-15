<?php

namespace Drupal\guardian;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * Interface GuardianManagerInterface
 * @package Drupal\guardian
 */
interface GuardianManagerInterface {

  /**
   * Set default Guarded User values.
   *
   * @param \Drupal\user\UserInterface $user
   * @return void
   */
  public function setDefaultUserValues(UserInterface $user);

  /**
   * Check if Account has correct mail, init, pass values.
   *
   * @param AccountInterface $account
   *   Account object.
   * @return bool
   */
  public function hasValidData(AccountInterface $account);

  /**
   * Check if Account has been active for minimum period.
   *
   * @param AccountInterface $account
   *   Account object.
   * @return bool
   */
  public function hasValidSession(AccountInterface $account);

  /**
   * Check if Account is a Guarded User.
   *
   * @param AccountInterface $account
   *   Account object.
   * @return bool
   */
  public function isGuarded(AccountInterface $account);

  /**
   * Destroy all sessions of given Account.
   *
   * @param AccountInterface $account
   *   Account object.
   * @return void
   */
  public function destroySession(AccountInterface $account);

  /**
   * Notify the current state of the module.
   *
   * @param boolean $isEnabled
   *   If the module is enabled or not.
   * @return void
   */
  public function notifyModuleState($isEnabled);

  /**
   * @param string[] $body
   *   Array of messages to include in the body of an e-mail.
   * @return void
   */
  public function addMetadataToBody(&$body);

  /**
   * Shows the logout message when Guardian destroys a current user session.
   * @return void
   */
  public function showLogoutMessage();

  /**
   * Get a list of guarded user ids.
   *
   * @return int[]
   *   List of uids.
   */
  public function getGuardedUids();
}
