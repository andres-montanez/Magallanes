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
                    . "This command does everything you want to\n"
                    . "\n"
                    . "Syntax:\n"
                    . "    mage example [light]\n"
                    . "\n"
                    . "Usage examples:\n"
                    . "    * Default command:\n"
                    . "        mage example\n"
                    . "    * Runs the command with lights:\n"
                    . "        mage example light\n"
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
                    . "Syntax:\n"
                    . "    mage example [light]\n"
                    . "\n"
                    . "Usage examples:\n"
                    . "    * Default command:\n"
                    . "        mage example\n"
                    . "    * Runs the command with lights:\n"
                    . "        mage example light\n"
            ],
            'no_examples' => [
                'helpMessage' => 'This command does everything you want to',
                'examples' => [],
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "This command does everything you want to\n"
                    . "\n"
                    . "Syntax:\n"
                    . "    mage example [light]\n"
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
                    . "This command does everything you want to\n"
                    . "\n"
                    . "Usage examples:\n"
                    . "    * Default command:\n"
                    . "        mage example\n"
                    . "    * Runs the command with lights:\n"
                    . "        mage example light\n"
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
                    . "This command does everything you want to\n"
                    . "\n"
                    . "Syntax:\n"
                    . "    mage example [light]\n"
                    . "\n"
                    . "Usage examples:\n"
                    . "    * Default command:\n"
                    . "        mage example\n"
                    . "    * Runs the command with lights:\n"
                    . "        mage example light\n"
            ],
            "only_help" => [
                'helpMessage' => 'This command does everything you want to',
                'examples' => [],
                'syntax' => '',
                'output' => "\n"
                    . "This command does everything you want to\n"
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
                    . "Usage examples:\n"
                    . "    * Default command:\n"
                    . "        mage example\n"
                    . "    * Runs the command with lights:\n"
                    . "        mage example light\n"
            ],
            "only_syntax" => [
                'helpMessage' => '',
                'examples' => [],
                'syntax' => 'mage example [light]',
                'output' => "\n"
                    . "Syntax:\n"
                    . "    mage example [light]\n"
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
