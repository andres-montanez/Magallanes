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

    private $helpMessage;
    private $usageExamples = [];
    private $syntaxMessage;

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

    public function setHelpMessage($message)
    {
        $this->helpMessage = $message;

        return $this;
    }

    public function addUsageExample($snippet, $description = '')
    {
        array_push($this->usageExamples, [$snippet, $description]);

        return $this;
    }

    public function setSyntaxMessage($message)
    {
        $this->syntaxMessage = $message;

        return $this;
    }

    public function getInfoMessage()
    {
        $indent = str_repeat(" ", 4);

        $output = "";

        if (!empty($this->helpMessage)) {
            $output .= "\n";
            $output .= "<cyan><bold>{$this->helpMessage}</bold></cyan>\n";
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

        return $output;
    }
}
