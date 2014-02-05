<?php
namespace Brown298\DataTablesBundle\Test;

use \Phake;
use \PHPUnit_Framework_TestCase;

/**
 * Class AbstractBaseTest
 *
 * @package Brown298\DataTablesBundle\Tests
 * @author John Brown <brown.john@gmail.com>
 */
abstract class AbstractBaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp
     *
     */
    public function setUp()
    {
        \Phake::initAnnotations($this);
        parent::setUp();
    }


    /**
     * getProtectedValue
     *
     * gets a protected property's value
     *
     * @param mixed $object
     * @param string $property
     * @return mixed
     */
    public function getProtectedValue($object, $property)
    {
        $refl = new \ReflectionObject($object);
        $property = $refl->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * setProtectedValue
     *
     * sets a protected property's value
     *
     * @param mixed $object
     * @param string $property
     * @param mixed $value
     */
    public function setProtectedValue($object, $property, $value)
    {
        $refl = new \ReflectionObject($object);
        $property = $refl->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * callProtected
     *
     * calls a protected method on an object
     *
     * @param mixed $object
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function callProtected($object, $method, array $args = array())
    {
        $refl = new \ReflectionObject($object);
        $method = $refl->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs(
            $object,
            $args
        );
        return $result;
    }
} 