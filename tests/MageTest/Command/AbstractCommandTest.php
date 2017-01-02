<?php

namespace MageTest\Command;

use Mage\Command\AbstractCommand;
use MageTest\TestHelper\BaseTest;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AbstractCommandTest
 * @package MageTest\Command
 * @author Jakub Turek <ja@kubaturek.pl>
 * @coversDefaultClass Mage\Command\AbstractCommand
 */
class AbstractCommandTest extends BaseTest
{
    /**
     * @var AbstractCommand|PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractCommand;

    /**
     * @before
     */
    public function before()
    {
        $this->abstractCommand = $this->getMockForAbstractClass('Mage\Command\AbstractCommand');
    }

    /**
     * @covers ::setConfig
     */
    public function testSetConfig()
    {
        $configMock = $this->getMock('Mage\Config');
        $this->doTestSetter($this->abstractCommand, 'config', $configMock);
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $configMock = $this->getMock('Mage\Config');
        $this->doTestGetter($this->abstractCommand, 'config', $configMock);
    }

    public function infoMessageProvider()
    {
        return array(
            'happy_path' => array(
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => array(
                    array(
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ),
                    array(
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    )
                ),
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_blue>This command does everything you want to</light_blue>\n"
                    . "\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
                    . "\n"
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ),
            'no_help_message' => array(
                'name' => 'Example command',
                'helpMessage' => '',
                'examples' => array(
                    array(
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ),
                    array(
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    )
                ),
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
                    . "\n"
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ),
            'no_examples' => array(
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => array(),
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_blue>This command does everything you want to</light_blue>\n"
                    . "\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
            ),
            "no_syntax" => array(
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => array(
                    array(
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ),
                    array(
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    )
                ),
                'syntax' => '',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_blue>This command does everything you want to</light_blue>\n"
                    . "\n"
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ),
            "stripping_colons" => array(
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => array(
                    array(
                        'snippet' => 'mage example',
                        'description' => 'Default command : '
                    ),
                    array(
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights:  '
                    )
                ),
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_blue>This command does everything you want to</light_blue>\n"
                    . "\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
                    . "\n"
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ),
            "only_help" => array(
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => array(),
                'syntax' => '',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_blue>This command does everything you want to</light_blue>\n"
            ),
            "only_examples" => array(
                'name' => 'Example command',
                'helpMessage' => '',
                'examples' => array(
                    array(
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ),
                    array(
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    )
                ),
                'syntax' => '',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ),
            "only_syntax" => array(
                'name' => 'Example command',
                'helpMessage' => '',
                'examples' => array(),
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
            ),
            "no_name" => array(
                'name' => '',
                'helpMessage' => 'This command does everything you want to',
                'examples' => array(
                    array(
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ),
                    array(
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    )
                ),
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<light_blue>This command does everything you want to</light_blue>\n"
                    . "\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
                    . "\n"
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ),
            "no_info_at_all" => array(
                'name' => '',
                'helpMessage' => '',
                'examples' => array(),
                'syntax' => '',
                'output' => "\n"
                    . "<red><bold>Sorry, there's no help for this command at the moment.</bold></red>\n"
            )
        );
    }

    /**
     * @covers ::getInfoMessage
     * @covers ::setHelpMessage
     * @covers ::addUsageExample
     * @covers ::setSyntaxMessage
     * @covers ::setName
     *
     * @dataProvider infoMessageProvider
     */
    public function testGetInfoMessage($name, $helpMessage, $examples, $syntax, $expectedMessage)
    {
        /** @var AbstractCommand $command */
        $command = $this->getMockForAbstractClass('Mage\Command\AbstractCommand');

        $command->setName($name);

        foreach ($examples as $example) {
            $command->addUsageExample($example['snippet'], $example['description']);
        }

        $command->setHelpMessage($helpMessage);
        $command->setSyntaxMessage($syntax);

        $actualMessage = $command->getInfoMessage();
        $this->assertEquals($expectedMessage, $actualMessage);
    }
}
