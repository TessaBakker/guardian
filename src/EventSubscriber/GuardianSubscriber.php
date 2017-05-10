<?php

namespace Drupal\guardian\EventSubscriber;

use \Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\guardian\GuardianManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class guardianSubscriber
 * @package Drupal\guardian\EventSubscriber
 */
class GuardianSubscriber implements EventSubscriberInterface {

  protected $guardianManager;

  protected $currentUser;

  /**
   * GuardianSubscriber constructor.
   */
  function __construct(GuardianManagerInterface $guardianManager, AccountProxyInterface $accountProxy) {
    $this->guardianManager = $guardianManager;
    $this->currentUser = $accountProxy;
  }

  /**
   * Returns password reset page if the current Guarded User is invalid.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function checkUser(GetResponseEvent $event) {
    $current_path = Url::fromRoute('<current>');

    // Show message to guarded users that are logged out with force.
    $guardian_message = $event->getRequest()->get('guardian_redirect', 0);
    if ($guardian_message) {
      $this->guardianManager->showLogoutMessage();
      return;
    }

    // Skip user/reset url token.
    if (strpos($current_path->getInternalPath(), 'user/reset') === 0) {
      return;
    }

    // Skip non-guarded users.
    if (!$this->guardianManager->isGuarded($this->currentUser)) {
      return;
    }

    // Skip valid guarded users.
    if ($this->guardianManager->hasValidSession($this->currentUser) && $this->guardianManager->hasValidData($this->currentUser)) {
      return;
    }

    // Destroy session and redirect to password reset.
    $this->guardianManager->destroySession($this->currentUser);

    $password_url = Url::fromRoute('user.pass');
    $password_url->setRouteParameters(['destination' => $current_path->getInternalPath(), 'guardian_redirect' => 1]);

    $response = new RedirectResponse($password_url->toString());
    $event->setResponse($response);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = 'checkUser';
    return $events;
  }
}
