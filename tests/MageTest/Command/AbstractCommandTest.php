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
                    . "<cyan><bold>This command does everything you want to</bold></cyan>\n"
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
                'helpMessage' => 'This command does everything you want to',
                'examples' => [],
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>This command does everything you want to</bold></cyan>\n"
                    . "\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
            ],
            "no_syntax" => [
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
                    . "<cyan><bold>This command does everything you want to</bold></cyan>\n"
                    . "\n"
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ],
            "stripping_colons" => [
                'helpMessage' => 'This command does everything you want to',
                'examples' => [
                    [
                        'snippet' => 'mage example',
                        'description' => 'Default command:'
                    ],
                    [
                        'snippet' => 'mage example light',
                        'description' => 'Runs the command with lights:'
                    ]
                ],
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<cyan><bold>This command does everything you want to</bold></cyan>\n"
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
                'helpMessage' => 'This command does everything you want to',
                'examples' => [],
                'syntax' => '',
                'output' => "\n"
                    . "<cyan><bold>This command does everything you want to</bold></cyan>\n"
            ],
            "only_examples" => [
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
                    . "<light_gray><bold>Usage examples:</bold></light_gray>\n"
                    . "    * Default command:\n"
                    . "        <green>mage example</green>\n"
                    . "    * Runs the command with lights:\n"
                    . "        <green>mage example light</green>\n"
            ],
            "only_syntax" => [
                'helpMessage' => '',
                'examples' => [],
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "<light_gray><bold>Syntax:</bold></light_gray>\n"
                    . "    <light_green>mage example [light]</light_green>\n"
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
    public function testGetInfoMessage($helpMessage, $examples, $syntax, $expectedMessage)
    {
        /** @var AbstractCommand $command */
        $command = $this->getMockForAbstractClass('Mage\Command\AbstractCommand');

        foreach ($examples as $example) {
            $command->addUsageExample($example['snippet'], $example['description']);
        }

        $command->setHelpMessage($helpMessage);
        $command->setSyntaxMessage($syntax);

        $actualMessage = $command->getInfoMessage();
        $this->assertEquals($expectedMessage, $actualMessage);

    }
}
