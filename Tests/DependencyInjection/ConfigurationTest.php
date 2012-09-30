<?php
namespace ERD\DoctrineEntityValidationBundle\Tests\DependencyInjection;

/**
 * Description of ConfigurationTest
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 16, 2012 Ethan Resnick Design
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $processor;
    protected $config;
    
    public function setUp()
    {
        $this->processor = new \Symfony\Component\Config\Definition\Processor();
        $this->config    = new \ERD\DoctrineEntityValidationBundle\DependencyInjection\Configuration();
    }


    public function validConfigProvider()
    {
        $configs = array();
        $configs[] = array(array('dont_validate'=>array()), array());
        $configs[] = array(array('connection'=>'something'));
        $configs[] = array(array('dont_validate'=>array()), array('connection'=>'something'));

        foreach($configs as &$config) { $config = array($config); /* wrap to make an array of args. */ }
        return $configs;
    }
    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationRejectsInvalidKeys()
    {
        $config = $this->processor->processConfiguration($this->config, array(array('test'=>'yes')));
    }
    
    /**
     * @dataProvider validConfigProvider
     */
    public function testConfigurationTakesValidConfigurations($config)
    {
        try { $this->processor->processConfiguration($this->config, $config); }
        catch(\Exception $e) {$this->fail('No exception should be thrown for valid configs.'); }
    }
}