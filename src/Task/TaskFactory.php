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
    protected Runtime $runtime;

    /**
     * @var AbstractTask[] Registered Tasks
     */
    protected array $registeredTasks = [];

    /**
     * Constructor
     */
    public function __construct(Runtime $runtime)
    {
        $this->runtime = $runtime;
        $this->loadBuiltInTasks();
        $this->loadCustomTasks($runtime->getConfigOption('custom_tasks', []));
    }

    /**
     * Add a Task
     */
    public function add(AbstractTask $task): void
    {
        $task->setRuntime($this->runtime);
        $this->registeredTasks[$task->getName()] = $task;
    }

    /**
     * Get a Task by it's registered Name/Code, or it can be a Class Name,
     * in that case the class will be instantiated
     *
     * @param string|mixed[] $name
     * @throws RuntimeException
     */
    public function get(mixed $name): AbstractTask
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
    protected function loadBuiltInTasks(): void
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/BuiltIn')->name('*Task.php');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $taskClass = substr(
                '\\Mage\\Task\\BuiltIn\\' . str_replace(
                    '/',
                    '\\',
                    $file->getRelativePathname()
                ),
                0,
                -4
            );
            if (class_exists($taskClass)) {
                $reflex = new ReflectionClass($taskClass);
                if ($reflex->isInstantiable()) {
                    $task = new $taskClass();
                    if ($task instanceof AbstractTask) {
                        $this->add($task);
                    }
                }
            }
        }
    }

    /**
     * Load Custom Tasks
     *
     * @param string[] $tasksToLoad
     * @throws RuntimeException
     */
    protected function loadCustomTasks(array $tasksToLoad): void
    {
        foreach ($tasksToLoad as $taskClass) {
            if (!class_exists($taskClass)) {
                throw new RuntimeException(sprintf('Custom Task "%s" does not exists.', $taskClass));
            }

            $reflex = new ReflectionClass($taskClass);
            if (!$reflex->isInstantiable()) {
                throw new RuntimeException(sprintf('Custom Task "%s" can not be instantiated.', $taskClass));
            }

            $task = new $taskClass();
            if (!$task instanceof AbstractTask) {
                throw new RuntimeException(
                    sprintf('Custom Task "%s" must inherit "Mage\\Task\\AbstractTask".', $taskClass)
                );
            }

            // Add Task
            $this->add($task);
        }
    }
}
