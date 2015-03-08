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
        return [
            'happy_path' => [
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => [
                    [
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ],
                    [
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    ]
                ],
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
            ],
            'no_help_message' => [
                'name' => 'Example command',
                'helpMessage' => '',
                'examples' => [
                    [
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ],
                    [
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    ]
                ],
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
            ],
            'no_examples' => [
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => [],
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_blue>This command does everything you want to</light_blue>\n"
                    . "\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
            ],
            "no_syntax" => [
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => [
                    [
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ],
                    [
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    ]
                ],
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
            ],
            "stripping_colons" => [
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => [
                    [
                        'snippet' => 'mage example',
                        'description' => 'Default command : '
                    ],
                    [
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights:  '
                    ]
                ],
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
            ],
            "only_help" => [
                'name' => 'Example command',
                'helpMessage' => 'This command does everything you want to',
                'examples' => [],
                'syntax' => '',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_blue>This command does everything you want to</light_blue>\n"
            ],
            "only_examples" => [
                'name' => 'Example command',
                'helpMessage' => '',
                'examples' => [
                    [
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ],
                    [
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    ]
                ],
                'syntax' => '',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ],
            "only_syntax" => [
                'name' => 'Example command',
                'helpMessage' => '',
                'examples' => [],
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>Command: </bold></cyan>Example command\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
            ],
            "no_name" => [
                'name' => '',
                'helpMessage' => 'This command does everything you want to',
                'examples' => [
                    [
                        'snippet' => 'mage example',
                        'description' => 'Default command'
                    ],
                    [
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights'
                    ]
                ],
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
            ]
        ];
    }

    /**
     * @covers ::getInfoMessage
     * @covers ::setHelpMessage
     * @covers ::addUsageExample
     * @covers ::setSyntaxMessage
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
