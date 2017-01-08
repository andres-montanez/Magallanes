<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task;

use Mage\Runtime\Runtime;
use Mage\Runtime\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use ReflectionClass;

/**
 * Task Factory
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class TaskFactory
{
    /**
     * @var Runtime
     */
    protected $runtime;

    /**
     * @var array Registered Tasks
     */
    protected $registeredTasks = [];

    /**
     * Constructor
     *
     * @param Runtime $runtime
     */
    public function __construct(Runtime $runtime)
    {
        $this->runtime = $runtime;
        $this->loadBuiltInTasks();
    }

    /**
     * Add a Task
     *
     * @param AbstractTask $task
     */
    public function add(AbstractTask $task)
    {
        $task->setRuntime($this->runtime);
        $this->registeredTasks[$task->getName()] = $task;
    }

    /**
     * Get a Task by it's registered Name/Code, or it can be a Class Name,
     * in that case the class will be instantiated
     *
     * @param string $name Name/Code or Class of the Task
     * @return AbstractTask
     * @throws RuntimeException
     */
    public function get($name)
    {
        $options = [];
        if (is_array($name)) {
            $options = $name;
            list($name) = array_keys($name);
            $options = $options[$name];
        }

        if (array_key_exists($name, $this->registeredTasks)) {
            /** @var AbstractTask $task */
            $task = $this->registeredTasks[$name];
            $task->setOptions($options);
            return $task;
        } elseif (class_exists($name)) {
            $reflex = new ReflectionClass($name);
            if ($reflex->isInstantiable()) {
                $task = new $name();
                if ($task instanceof AbstractTask) {
                    $task->setOptions($options);
                    $this->add($task);
                    return $task;
                }
            }
        }

        throw new RuntimeException(sprintf('Invalid task name "%s"', $name));
    }

    /**
     * Load BuiltIn Tasks
     */
    protected function loadBuiltInTasks()
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/BuiltIn')->name('*Task.php');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $class = substr('\\Mage\\Task\\BuiltIn\\' . str_replace('/', '\\', $file->getRelativePathname()), 0, -4);
            if (class_exists($class)) {
                $reflex = new ReflectionClass($class);
                if ($reflex->isInstantiable()) {
                    $task = new $class();
                    if ($task instanceof AbstractTask) {
                        $this->add($task);
                    }
                }
            }
        }
    }
}
