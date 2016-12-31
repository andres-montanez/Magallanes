<?php

namespace Mage\Task\BuiltIn\Composer;

use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Composer Task - Generate Autoload
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class GenerateAutoloadTask extends AbstractTask
{
    public function getName()
    {
        return 'composer/generate-autoload';
    }

    public function getDescription()
    {
        return '[Composer] Generate Autoload';
    }

    public function execute()
    {
        $options = $this->getOptions();
        $command = $options['path'] . ' dumpautoload ' . $options['flags'];

        /** @var Process $process */
        $process = $this->runtime->runCommand($command);

        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $userOptions = $this->runtime->getConfigOptions('composer', []);
        $options = array_merge(
            ['path' => 'composer', 'flags' => '--optimize'],
            (is_array($userOptions) ? $userOptions : []),
            $this->options
        );

        return $options;
    }
}
