<?php
 
/**
 * @file
 *
 * Contains \Drupal\Tests\test_example\Unit\TestExampleConversionsTest.
 */
 
namespace Drupal\Tests\guardian\Unit;
 
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\guardian\Guardian;
use Drupal\Tests\UnitTestCase;
 
/**
 * PHPUnit to test reset of User 1.
 */
class ResetUser1Test extends UnitTestCase {

  /**
   * @var Guardian $guardian
   */
  protected $guardian;

  /**
   * @var translationManager $translationManager
   */
  protected $translationManager;

  /**
   * @var container $container
   */
  protected $container;

  /**
   * Setup.
   */
  public function setUp() {
    parent::setUp();

    $this->guardian = new Guardian();

    // Example of an translationManager mock which is needed of the class you test uses t().
    $this->translationManager = $this->getMockBuilder('\Drupal\Core\StringTranslation\TranslationManager')
      ->disableOriginalConstructor()
      ->getMock();

    // Create a dummy container.
    $this->container = new ContainerBuilder();
    $this->container->set('string_translation', $this->translationManager);
    \Drupal::setContainer($this->container);

  }
 
  /**
   * Test if resetting user#1 actually works.
   */
  public function testResetUser1() {
    // TODO
  }
 
}