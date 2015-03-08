<?php

namespace MageTest\TestHelper;

/**
 * Class BaseTest
 *
 * Class containing common methods useful for unit testing.
 * Since Magallanes keeps compatibility with PHP 5.3, those methods can't be moved to a trait.
 * This class extends \PHPUnit_Framework_TestCase so it can be used with any test class.
 *
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

    /**
     * Disable logging to log file and turn off colors
     *
     * @before
     */
    protected function setUpConsoleStatics()
    {
        $consoleReflection = new \ReflectionClass('Mage\Console');
        $logEnableProperty = $consoleReflection->getProperty('logEnabled');
        $logEnableProperty->setAccessible(true);
        $logEnableProperty->setValue(false);

        $configMock = $this->getMock('Mage\Config');
        $configMock->expects($this->any())
            ->method('getParameter')
            ->with('no-color')
            ->willReturn(true);

        $configProperty = $consoleReflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($configMock);
    }

    /**
     * Tests getter of given object for given property name and example value
     *
     * @param object $object Object instance
     * @param string $propertyName Property name
     * @param mixed $propertyValue Value to set
     */
    final protected function doTestGetter($object, $propertyName, $propertyValue)
    {
        $this->setPropertyValue($object, $propertyName, $propertyValue);
        $getterName = $this->getGetterName($propertyName);

        $actual = $object->$getterName();

        $this->assertSame($propertyValue, $actual);
    }

    /**
     * Tests setter of given object for given property name and example value
     *
     * @param object $object Object instance
     * @param string $propertyName Property name
     * @param mixed $propertyValue Value to set
     */
    final protected function doTestSetter($object, $propertyName, $propertyValue)
    {
        $setterName = $this->getSetterName($propertyName);
        $object->$setterName($propertyValue);

        $actual = $this->getPropertyValue($object, $propertyName);
        $this->assertSame($propertyValue, $actual);
    }

    /**
     * Returns the conventional getter name for given property name
     *
     * @param string $propertyName Property name
     * @return string Getter method name
     */
    private function getGetterName($propertyName)
    {
        return 'get' . ucfirst($propertyName);
    }

    /**
     * Returns the conventional setter name for given property name
     *
     * @param string $propertyName Property name
     * @return string Getter method name
     */
    private function getSetterName($propertyName)
    {
        return 'set' . ucfirst($propertyName);
    }
}
