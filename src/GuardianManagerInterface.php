<?php

namespace Drupal\guardian;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * Interface GuardianManagerInterface.
 *
 * @package Drupal\guardian
 */
interface GuardianManagerInterface {

  /**
   * Set default Guarded User values.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object to set with default values.
   */
  public function setDefaultUserValues(UserInterface $user);

  /**
   * Check if Account has correct mail, init, pass values.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   *
   * @return bool
   *   True for valid, false for invalid.
   */
  public function hasValidData(AccountInterface $account);

  /**
   * Check if Account has been active for minimum period.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   *
   * @return bool
   *   True for valid, false for invalid.
   */
  public function hasValidSession(AccountInterface $account);

  /**
   * Check if Account is a Guarded User.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   *
   * @return bool
   *   True for guarded, false for un-guarded.
   */
  public function isGuarded(AccountInterface $account);

  /**
   * Destroy all sessions of given Account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   */
  public function destroySession(AccountInterface $account);

  /**
   * Notify the current state of the module.
   *
   * @param bool $isEnabled
   *   If the module is enabled or not.
   */
  public function notifyModuleState($isEnabled);

  /**
   * Add meta data to the body of the mail.
   *
   * @param string[] $body
   *   Array of messages to include in the body of an e-mail.
   */
  public function addMetadataToBody(array &$body);

  /**
   * Shows the logout message when Guardian destroys a current user session.
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
