<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Command;

use Mage\Config;

/**
 * Abstract Class for a Magallanes Command
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
abstract class AbstractCommand
{
    /**
     * Instance of the loaded Configuration.
     *
     * @var \Mage\Config
     */
    protected $config = null;

    /**
     * Command's help message
     *
     * @var string
     */
    private $helpMessage;

    /**
     * Usage examples.
     *
     * @var array
     */
    private $usageExamples = array();

    /**
     * Command's syntax message
     *
     * @var string
     */
    private $syntaxMessage;

    /**
     * Command name
     *
     * @var string
     */
    private $name;

    /**
     * Runs the Command
     * @return integer exit code
     * @throws \Exception
     */
    abstract public function run();

    /**
     * Sets the Loaded Configuration.
     *
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Gets the Loaded Configuration.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets command name
     *
     * @param string $name Command name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets command's help message
     *
     * @param string $message Command's help message
     * @return $this
     */
    public function setHelpMessage($message)
    {
        $this->helpMessage = $message;

        return $this;
    }

    /**
     * Adds command's usage example
     *
     * @param string $snippet Example's snippet
     * @param string $description Example's description
     * @return $this
     */
    public function addUsageExample($snippet, $description = '')
    {
        array_push($this->usageExamples, array($snippet, $description));

        return $this;
    }

    /**
     * Sets command's syntax message
     *
     * @param string $message Syntax message
     * @return $this
     */
    public function setSyntaxMessage($message)
    {
        $this->syntaxMessage = $message;

        return $this;
    }

    /**
     * Returns formatted command info
     *
     * @return string
     */
    public function getInfoMessage()
    {
        $indent = str_repeat(" ", 4);

        $output = "";
        if (!empty($this->name)) {
            $output .= "\n";
            $output .= "<cyan><bold>Command: </bold></cyan>";
            $output .= $this->name;
        }

        if (!empty($this->helpMessage)) {
            $output .= "\n";
            $output .= "<light_blue>{$this->helpMessage}</light_blue>\n";
        }

        if (!empty($this->syntaxMessage)) {
            $output .= "\n";
            $output .= "<light_gray><bold>Syntax:</bold></light_gray>\n";
            $output .= "$indent<light_green>{$this->syntaxMessage}</light_green>";
            $output .= "\n";
        }

        if (!empty($this->usageExamples)) {
            $output .= "\n";
            $output .= "<light_gray><bold>Usage examples:</bold></light_gray>\n";
            foreach ($this->usageExamples as $example) {
                $snippet = $example[0];
                $description = $example[1];
                $output .= "$indent* ";
                if (!empty($description)) {
                    $description = rtrim($description, ': ') . ":";
                    $output .= $description;
                    $output .= "\n$indent$indent";
                }

                $output .= "<green>$snippet</green>";
                $output .= "\n";
            }
        }

        if (empty($output)) {
            $output .= "\n";
            $output .= "<red><bold>Sorry, there's no help for this command at the moment.</bold></red>";
            $output .= "\n";
        }

        return $output;
    }
}
