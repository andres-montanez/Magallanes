<?php

namespace MageTest\TestHelper;

/**
 * Class BaseTest
 * @package MageTest\TestHelper
 * @author Jakub Turek <ja@kubaturek.pl>
 */
abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns value of non-public property from given class
     *
     * @param string|object $object Object instance or class name
     * @param string $propertyName Class' or object's property name
     * @return mixed
     */
    final protected function getPropertyValue($object, $propertyName)
    {
        $configProperty = new \ReflectionProperty($object, $propertyName);
        $configProperty->setAccessible(true);

        return  $configProperty->getValue($object);
    }

    /**
     * Sets value to given property and given object
     *
     * @param object $object Object instance
     * @param string $propertyName Property name
     * @param mixed $value Value to set
     */
    final protected function setPropertyValue($object, $propertyName, $value)
    {
        $configProperty = new \ReflectionProperty($object, $propertyName);
        $configProperty->setAccessible(true);
        $configProperty->setValue($object, $value);
    }
}
