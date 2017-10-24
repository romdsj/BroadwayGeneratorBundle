<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Tests;

use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator\CommandHandlerManipulator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group command-handler
 */
class CommandHandlerManipulatorTest extends \PHPUnit_Framework_TestCase
{
    protected $filesystem;
    protected $tmpDir;

    public function setUp()
    {
        $this->tmpDir = sys_get_temp_dir().'/sf';
        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->tmpDir);
        $this->filesystem->mkdir($this->tmpDir);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->tmpDir);
    }

    /**
     * @dataProvider getEventsName
     */
    public function testAddHandleMethods($eventName, $namespace, $className, $startingContents, $expectedContents)
    {
        $targetPath = $this->tmpDir . '/Command' . $className . '.php';
        file_put_contents($targetPath, $startingContents);

        require_once($targetPath);

        $testCommandHandlerClassName = $namespace. '\\' . $className;
        $testCommandHandler = new $testCommandHandlerClassName();
        $manipulator = new CommandHandlerManipulator($testCommandHandler);

        $manipulator->addHandlerMethod($eventName);

        $realContents = file_get_contents($targetPath);
        $this->assertEquals($expectedContents, $realContents);
    }

    public function getEventsName()
    {
        return [
            [
                'FooBar',
                'Foo\Bar',
                'FooBarCommandHandler',
                <<<EOF
<?php

namespace Foo\Bar;

use Broadway\CommandHandling\SimpleCommandHandler;

class FooBarCommandHandler extends SimpleCommandHandler
{
}
EOF
                , <<<EOF
<?php

namespace Foo\Bar;

use Broadway\CommandHandling\SimpleCommandHandler;

class FooBarCommandHandler extends SimpleCommandHandler
{

    public function handleFooBar(FooBarCommand \$command)
    {
        // @TODO Insert your code here
    }
}
EOF
            ],
            [
                'FooBarSecond',
                'Foo\Bar\Second',
                'FooBarSecondCommandHandler',
                <<<EOF
<?php

namespace Foo\Bar\Second;

use Broadway\CommandHandling\SimpleCommandHandler;

class FooBarSecondCommandHandler extends SimpleCommandHandler
{
    public function handleOther(OtherCommand \$command)
    {
        \$code = 'Some code';
    }
}
EOF
                , <<<EOF
<?php

namespace Foo\Bar\Second;

use Broadway\CommandHandling\SimpleCommandHandler;

class FooBarSecondCommandHandler extends SimpleCommandHandler
{
    public function handleOther(OtherCommand \$command)
    {
        \$code = 'Some code';
    }

    public function handleFooBarSecond(FooBarSecondCommand \$command)
    {
        // @TODO Insert your code here
    }
}
EOF
            ]
        ];
    }
}