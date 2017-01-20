<?php

namespace Mage\Task\BuiltIn\Database\Phinx;

use Mage\Task\AbstractTask;

/**
 * Abstract Task for Phinx
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
abstract class PhinxAbstractTask extends AbstractTask
{
    /**
     * Configuration option.
     *
     * @var string
     */
    private $configuration;

    /**
     * Environnement option.
     *
     * @var string
     */
    private $environment;

    /**
     * Parser option.
     *
     * @var string
     */
    private $parser;

    /**
     * Target option.
     *
     * @var string
     */
    private $target;

    /**
     *
     */
    public function init()
    {
        foreach ($this->getOptionsNames() as $optionName) {
            $this->$optionName = $this->getParameter($optionName);
        }
    }

    /**
     * @return string
     */
    protected function getPhinxCmd()
    {
        return $this->getParameter('phinx_cmd', $this->getConfig()->general('phinx_cmd', 'vendor/bin/phinx'));
    }

    /**
     * @return string
     */
    protected function getOptionsForCmd()
    {
        $optionsForCmd = '';
        foreach ($this->getOptionsNames() as $optionName) {
            if ($this->$optionName) {
                $optionsForCmd .= '--' . $optionName . ' ' . $this->$optionName . ' ';
            }
        }

        // Will add extra options to command line coming from the cli
        foreach ($this->getCliOptionsNames() as $argument => $optionName) {
            if ($value = $this->findArgument($argument)) {
                $optionsForCmd .= '--' . $optionName . ' ' . $value;
            }
        }

        return $optionsForCmd;
    }

    /**
     * Tries to find an argument in the command line arguments.
     *
     * For example if command line argument has : phinx-target:20151009125450 it
     * will find it and return 20151009125450 if $argument == 'phinx-target'.
     *
     * @param string $argument
     *
     * @return false|string
     */
    protected function findArgument($argument)
    {
        $value = false;
        foreach ($this->getConfig()->getArguments() as $cmdArgument) {
            if (strpos($cmdArgument . ':', $argument) === 0) {
                $value = trim(substr($cmdArgument, strlen($argument . ':')));
                break;
            }
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function getOptionsNames()
    {
        return array('configuration', 'environment', 'parser', 'target');
    }

    /**
     * Arguments that can be sent at runtime via the cli mage call like
     * phinx-target:20151002103807
     *
     * @return array
     */
    protected function getCliOptionsNames()
    {
        return array('phinx-target' => 'target');
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string    $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string    $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param string    $parser
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string    $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }
}
