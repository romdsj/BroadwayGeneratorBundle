<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Tests;

use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator\ServiceConfigurationManipulator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group command-handler
 */
class ServiceConfigurationManipulatorTest extends \PHPUnit_Framework_TestCase
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
    public function testAddServiceConfiguration($readModelName, $namespace, $startingContents, $expectedContents)
    {
        $targetPath = $this->tmpDir . '/services.xml';
        file_put_contents($targetPath, $startingContents);

        $manipulator = new ServiceConfigurationManipulator($targetPath);

        $manipulator->addServiceConfiguration($namespace, $readModelName);

        $realContents = file_get_contents($targetPath);
        
        $this->assertEquals($expectedContents, $realContents);
    }

    public function getEventsName()
    {
        return [
            [
                'FooBar',
                'Foo\Bar',
'<?xml version="1.0"?>
<response xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
  <services>
    <defaults public="false"/>
  </services>
</response>
'
                ,
'<?xml version="1.0"?>
<response xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
  <services>
    <defaults public="false"/>
    <service id="foobar.readmodel" class="Broadway\ReadModel\ReadModel">
      <factory method="create" service="broadway.read_model.repository_factory"/>
      <argument>foobar.readmodel</argument>
      <argument>Foo\Bar\ReadModel\FooBarReadModel</argument>
    </service>
  </services>
</response>
'
            ],
            [
                'FooBarSecond',
                'Foo\Bar\Second',
'<?xml version="1.0"?>
<response xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
  <services>
    <defaults public="false"/>
  </services>
</response>
'
                ,
'<?xml version="1.0"?>
<response xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
  <services>
    <defaults public="false"/>
    <service id="foobarsecond.readmodel" class="Broadway\ReadModel\ReadModel">
      <factory method="create" service="broadway.read_model.repository_factory"/>
      <argument>foobarsecond.readmodel</argument>
      <argument>Foo\Bar\Second\ReadModel\FooBarSecondReadModel</argument>
    </service>
  </services>
</response>
'
            ],
        ];
    }
}