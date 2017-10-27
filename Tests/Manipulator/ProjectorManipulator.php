<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Tests;

use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator\ProjectorManipulator;
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
        $targetPath = $this->tmpDir . '/Event' . $className . '.php';
        file_put_contents($targetPath, $startingContents);

        require_once($targetPath);

        $testCommandHandlerClassName = $namespace. '\\' . $className;
        $testCommandHandler = new $testCommandHandlerClassName();
        $manipulator = new ProjectorManipulator($testCommandHandler);

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
                'FooBarProjector',
                <<<EOF
<?php

namespace Foo\Bar;

use Broadway\ReadModel\Projector;

class FooBarProjector extends Projector
{
}
EOF
                , <<<EOF
<?php

namespace Foo\Bar;

use Broadway\ReadModel\Projector;

class FooBarProjector extends Projector
{

    public function handleFooBar(FooBarEvent \$event)
    {
        // @TODO Insert your code here
    }
}
EOF
            ],
            [
                'FooBarSecond',
                'Foo\Bar\Second',
                'FooBarSecondProjector',
                <<<EOF
<?php

namespace Foo\Bar\Second;

use Broadway\ReadModel\Projector;

class FooBarSecondProjector extends Projector
{
    public function handleOther(OtherEvent \$event)
    {
        \$code = 'Some code';
    }
}
EOF
                , <<<EOF
<?php

namespace Foo\Bar\Second;

use Broadway\ReadModel\Projector;

class FooBarSecondProjector extends Projector
{
    public function handleOther(OtherEvent \$event)
    {
        \$code = 'Some code';
    }

    public function handleFooBarSecond(FooBarSecondEvent \$event)
    {
        // @TODO Insert your code here
    }
}
EOF
            ]
        ];
    }
}