<?php
namespace ERD\DoctrineEntityValidationBundle\Event;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
/**
 * Validates Doctrine-managed entities before they're persisted to the database.
 * 
 * While any form that adds entities should still do its own validation 
 * (to try to handle them more gracefully than we can here), but this acts as a 
 * last-stop fallback to ensure no bad data hits the db.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 16, 2012 Ethan Resnick Design
 */
class ValidationSubscriber implements \Doctrine\Common\EventSubscriber
{
    /** @var ValidatorInterface */
    protected $validator;
    
    protected $skip;
    
    /**
     * Constructor
     * 
     * @param ValidatorInterface $validator Symfony's validator service
     * @param array $classesToSkip An array of FCQNs of entities not to validate. 
     */
    public function __construct(ValidatorInterface $validator, array $classesToSkip)
    {
        $this->validator = $validator;
        
        foreach($classesToSkip as &$class)
        {
            //if the class name starts with a \, delete it because FCQNs, returned by get_class(),
            //always start from the global namespace by definition so don't need/have a leading \.
            if(substr($class,0,1)=='\\')
            {
                $class = substr($class, 1);
            }
        }
        
        $this->skip      = $classesToSkip;
    }
    
    public function getSubscribedEvents()
    {
        return array(Events::preUpdate, Events::prePersist);
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $changedFields = array_keys($eventArgs->getEntityChangeSet());

        foreach($changedFields as $field)
        {
            $this->validate($eventArgs->getEntity(), $field);
        }
    }
    
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->validate($eventArgs->getEntity());
    }
    
    protected function validate($entity, $property = false)
    {
        $class = get_class($entity);
        
        if (!in_array($class, $this->skip))
        {
            if($property) //validate just a single property
            {
                $constraintViolations = $this->validator->validateProperty($entity, $property);
            }
            else //validate the whole entity
            {
                $constraintViolations = $this->validator->validate($entity);
            }
            
            if(count($constraintViolations)) 
            {
                throw new ValidatorException('This '. $class.' Entity is invalid. Errors were: '.$constraintViolations);
            }
        }
    }
}