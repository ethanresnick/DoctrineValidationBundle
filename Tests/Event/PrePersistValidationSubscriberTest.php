<?php
namespace ERD\DoctrineEntityValidationBundle\Tests\Event;
use ERD\DoctrineEntityValidationBundle\Event\ValidationSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PrePersistValidationSubscriberTest extends WebTestCase
{
    protected $eventArgsDouble;
    protected $validatorMock;
    
    protected static $kernel;
    protected static $container;
    protected static $em;
    protected static $subscriberClass;
    
    public static function setUpBeforeClass()
    {
        static::$kernel = static::createKernel(); //make a test kernel
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();
        static::$em = static::$container->get('doctrine.orm.entity_manager');
        static::$subscriberClass = static::$container->getParameter('erd_doctrine_entity_validation.subscriber.class');
    }

    public function tearDown()
    {
        /* here so the parent tearDown, which assumes the kernel is setUp() before every test,
         * isn't called after every test; we only need to setUp and teardown the kernel once. 
         */
    }

    public static function tearDownAfterClass()
    {
        static::$em->getConnection()->close();
        static::$em = null;
        static::$container = null;
        static::$kernel->shutdown();
    }

    public function setUp()
    {
        $eventArgsDouble = $this->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')->disableOriginalConstructor()->getMock();
        $eventArgsDouble->expects($this->once())->method('getEntity')->will($this->returnValue(new \stdClass()));
        
        $validatorMock = $this->getMock('\Symfony\Component\Validator\ValidatorInterface');
        $validatorMock->expects($this->once())->method('validate');
        
        $this->eventArgsDouble = $eventArgsDouble;
        $this->validatorMock   = $validatorMock;
        
    }
     
    public function testValidationRunsOnEntityPrePersist()
    {
        $subscriber = new static::$subscriberClass($this->validatorMock, array()); 
        
        //dispatch the event manually, just to this subscriber, to see if our mocks/stubs work.
        $subscriber->prePersist($this->eventArgsDouble);
    }

}
