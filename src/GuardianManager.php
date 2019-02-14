<?php

namespace Drupal\guardian;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;
use Egulias\EmailValidator\EmailValidator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class GuardianManager.
 *
 * @package Drupal\guardian
 */
final class GuardianManager implements GuardianManagerInterface {

  use StringTranslationTrait, LoggerChannelTrait, MessengerTrait;

  /**
   * The configuration object factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The account object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * GuardianManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The account object.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Egulias\EmailValidator\EmailValidator $email_validator
   *   The email validator.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, MailManagerInterface $mail_manager, RequestStack $request_stack, AccountInterface $current_user, SessionManagerInterface $session_manager, TimeInterface $time, EmailValidator $email_validator, ModuleHandlerInterface $module_handler) {
    $this->configFactory = $config_factory;
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->mailManager = $mail_manager;
    $this->requestStack = $request_stack;
    $this->currentUser = $current_user;
    $this->sessionManager = $session_manager;
    $this->time = $time;
    $this->emailValidator = $email_validator;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function notifyModuleState($isEnabled) {
    $site = $this->configFactory->get('system.site')->get('name');

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
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->userStorage->load(1);

    $this->mailManager->mail('guardian', 'notification', $guardian_mail, $user->getPreferredLangcode(), $params, NULL, TRUE);
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
      '@ip' => $this->requestStack->getCurrentRequest()->getClientIp(),
    ]);
    $body[] = $this->t('Host name: @host', [
      '@host' => $this->requestStack->getCurrentRequest()->getHost(),
    ]);

    if (PHP_SAPI === 'cli') {
      $body[] = $this->t('Terminal user: @user', ['@user' => $_SERVER['USER'] ?: $this->t('Unknown')]);
    }

    $this->moduleHandler->alter('guardian_add_metadata_to_body', $body);
  }

  /**
   * {@inheritdoc}
   */
  public function destroySession(AccountInterface $account) {

    $this->sessionManager->delete($account->id());

    if ($account->id() == $this->currentUser->id()) {
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
    $this->messenger()->addWarning($message, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function hasValidData(AccountInterface $account) {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->userStorage->load($account->id());

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
    $timeout = $this->time->getRequestTime() - $guardian_seconds;
    return $account->getLastAccessedTime() > $timeout;
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
      $implementations = $this->moduleHandler->getImplementations('guardian_guarded_users');

      foreach ($implementations as $module) {
        $function = $module . '_guardian_guarded_users';
        $guarded_users = $function();
        foreach ($guarded_users as $uid => $mail) {
          if (empty($mail) || !is_int($uid) || $uid < 2 || !$this->emailValidator->isValid($mail)) {
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
