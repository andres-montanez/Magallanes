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
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
class TaskFactory
{
    /**
     * Runtime
     * @var Runtime
     */
    private $runtime;

    /**
     * Registered tasks
     * @var TaskInterface[]
     */
    private $tasks = [];

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
     *
     * @deprecated Deprecated since 3.0, to be removed in 4.0.
     *             Use the TaskFactory::addTask() instead.
     */
    public function add(AbstractTask $task)
    {
        @trigger_error(
            'Using TaskFactory::add() has been deprecated and will no longer work in 4.0. Use the TaskFactory::addTask() instead.',
            E_USER_DEPRECATED
        );

        $task->setRuntime($this->runtime);
        $this->tasks[$task->getName()] = $task;
    }

    /**
     * Add a task
     *
     * @param TaskInterface $task
     */
    public function addTask(TaskInterface $task)
    {
        $this->tasks[$task->getName()] = $task;
    }

    /**
     * Get a Task by it's registered Name/Code, or it can be a Class Name,
     * in that case the class will be instantiated
     *
     * @param string $name Name/Code or Class of the Task
     * @param array  $options
     *
     * @return TaskInterface
     *
     * @throws RuntimeException
     */
    public function get($name, array $options = null)
    {
        if (array_key_exists($name, $this->tasks)) {
            $task = $this->tasks[$name];
        } else {
            $task = $this->createTask($name);
        }

        $task->setRuntime($this->runtime);

        if ($options !== null) {
            $task->setOptions($options);
        }

        return $task;
    }

    /**
     * Load the built-in tasks
     *
     * @throws RuntimeException
     */
    private function loadBuiltInTasks()
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/BuiltIn')->name('*Task.php');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $class = substr('\\Mage\\Task\\BuiltIn\\'.str_replace('/', '\\', $file->getRelativePathname()), 0, -4);
            $reflex = new ReflectionClass($class);

            // Some classes found in the folder can be abstract
            if (!$reflex->isInstantiable()) {
                continue;
            }

            $this->createTask($class);
        }
    }

    /**
     * Create the task
     *
     * @param string $class
     *
     * @return TaskInterface
     *
     * @throws RuntimeException
     */
    private function createTask($class)
    {
        $this->validateTask($class);

        /** @var TaskInterface $task */
        $task = new $class();

        // Register the task
        $this->addTask($task);

        return $task;
    }

    /**
     * Validate the task
     *
     * @param string $class
     *
     * @throws RuntimeException
     */
    private function validateTask($class)
    {
        if (!class_exists($class)) {
            throw new RuntimeException(sprintf('The class "%s" does not exist', $class));
        }

        $reflex = new ReflectionClass($class);

        if (!$reflex->implementsInterface(TaskInterface::class)) {
            throw new RuntimeException(
                sprintf('The class "%s" must implement the "%s" interface', $class, TaskInterface::class)
            );
        }
    }
}
