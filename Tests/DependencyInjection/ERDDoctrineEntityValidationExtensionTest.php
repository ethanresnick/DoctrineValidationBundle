<?php
namespace ERD\DoctrineEntityValidationBundle\Tests\DependencyInjection;

use ERD\DoctrineEntityValidationBundle\DependencyInjection\ERDDoctrineEntityValidationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description of ERDDoctrineEntityValidationExtensionTest
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 16, 2012 Ethan Resnick Design
 */
class ERDDoctrineEntityValidationExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $testContainer;

    public function setUp()
    {
        $this->testContainer = new ContainerBuilder();
        $this->extension     = new ERDDoctrineEntityValidationExtension();
    }

    public function testDontValidateOptionRespectedInServiceDefinition()
    {
        $mockConfig = array(array('dont_validate'=>array('Test')));
        $this->extension->load($mockConfig, $this->testContainer);
        
        $this->assertEquals(array('Test'), $this->testContainer->getDefinition('erd_doctrine_entity_validation.validation_subscriber')->getArgument(1));
        
    }
    
    public function connectionConfigProvider()
    {
        return array(
            array(array(array('connection'=>'something')), 'something'),
            array(array(array()), false) //false means all connections/none specified explicitly.
        );
    }
    /**
     * @dataProvider connectionConfigProvider
     */
    public function testConnectionOptionRespectedInServiceDefinition($connectionConfig, $expectedConnection)
    {
        $this->extension->load($connectionConfig, $this->testContainer);
        
        $tag = $this->testContainer->getDefinition('erd_doctrine_entity_validation.validation_subscriber')->getTag('doctrine.event_subscriber');
        
        if($expectedConnection!== false)
        {
            $this->assertEquals($tag[0]['connection'], $expectedConnection);
        }
        else
        {
            $this->assertFalse(isset($tag[0]['connection']));
        }
    }
}