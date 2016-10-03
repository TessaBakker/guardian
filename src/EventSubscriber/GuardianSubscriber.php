<?php

namespace Drupal\guardian\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\guardian\Guardian;

/**
 * Class guardianSubscriber
 * @package Drupal\guardian\EventSubscriber
 */
class GuardianSubscriber implements EventSubscriberInterface {

  protected $account;
  protected $guardian;

  /**
   * GuardianSubscriber constructor.
   */
  function __construct() {
    $this->guardian = new Guardian();
  }

  /**
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function checkUser1(GetResponseEvent $event) {
    if ($this->guardian->guardian_account_is_root() && !$this->guardian->guardian_check_valid_session()) {
      $this->guardian->guardian_destroy_user1_sessions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkUser1');
    return $events;
  }

}