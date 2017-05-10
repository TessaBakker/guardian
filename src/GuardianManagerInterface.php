<?php

namespace Drupal\guardian;


use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

interface GuardianManagerInterface {

  /**
   * @param \Drupal\user\UserInterface $user
   * @return UserInterface
   */
  public function defaultUserValues(UserInterface $user);

  public function hasValidData(AccountInterface $user);

  public function hasValidSession(AccountInterface $account);

  public function isGuarded(AccountInterface $account);

  public function destroySession(AccountInterface $account);

  public function mailStatusUpdate($status, AccountInterface $account);

  public function addMetadataToBody(&$body);

  public function showLogoutMessage();

  public function getGuardedUids();
}