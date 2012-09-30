<?php
namespace ERD\DoctrineEntityValidationBundle\Tests\Event;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 17, 2012 Ethan Resnick Design
 */
class AllEventsValidationSubscriberTest extends WebTestCase
{
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


    /**
     * Since we're distributing this bundle to have it mixed in with other people's code, 
     * and they may try to run our tests with god knows what setup, it's too risky to actually
     * create new entity classes or objects or actually do persistence. So we just do a check
     * that doctrine agrees things are registered.
     */
    public function eventsProvider()
    {   
        return array(
          array(Events::preUpdate, '\Doctrine\ORM\Event\PreUpdateEventArgs'),
          array(Events::prePersist, '\Doctrine\ORM\Event\LifecycleEventArgs')
        );
    }

    /**
     * @dataProvider eventsProvider
     */
    public function testValidationSubscriberListensToProperEvents($event, $argsClass)
    {
        //we can't get the service itself (that's private), so we check for a listener of its class.
        foreach(static::$em->getEventManager()->getListeners($event) as $listener)
        {
            if($listener instanceof static::$subscriberClass)
            {
                return true;
            }
        }

        $this->fail('No listener of the proper class is registered for the '.$event.' event.');        
    }


    /**
     * @dataProvider eventsProvider 
     */
    public function testValidationSubscriberSkipsRequestedClasses($event, $argsClass)
    {
        $validatorMock = $this->getMock('\Symfony\Component\Validator\ValidatorInterface');
        $validatorMock->expects($this->never())->method('validate');
        $validatorMock->expects($this->never())->method('validateProperty');
        
        $eventArgsStub = $this->getMockBuilder($argsClass)->disableOriginalConstructor()->getMock();
        $eventArgsStub->expects($this->any())->method('getEntity')->will($this->returnValue(new \stdClass()));
        $eventArgsStub->expects($this->any())->method('getEntityChangeSet')->will($this->returnValue(array('field'=>'bs change data')));

        $subscriber = new static::$subscriberClass($validatorMock, array('stdClass'));
        
        //dispatch the event manually, just to this subscriber, to see if our mocks/stubs work.
        $subscriber->$event($eventArgsStub);
    }
}