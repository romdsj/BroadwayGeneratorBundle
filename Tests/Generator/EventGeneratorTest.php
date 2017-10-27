<?php

namespace Sensio\Bundle\GeneratorBundle\Tests\Generator;


use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator\EventGenerator;

class EventGeneratorTest extends GeneratorTest
{
    public function testGenerateController()
    {
        $eventName = 'FooBar';
        $eventFile = 'Event/FooBarEvent.php';
        $eventPath = $this->tmpDir.'/'.$eventFile;

        $this->getGenerator()->generate($this->getBundle(), $eventName);

        $this->assertTrue(file_exists($eventPath), sprintf('%s file has been generated.', $eventFile));

        $eventContent = file_get_contents($commandPath);
        $strings = array(
            'namespace Foo\\BarBundle\\Event',
            'class FooBarEvent',
            'private $uuid;',
            'public function __construct($uuid)',
            '$this->uuid = $uuid;',
            'public function getUUID()',
            'return $this->uuid;'
        );
        foreach ($strings as $string) {
            $this->assertContains($string, $eventContent);
        }
    }

    protected function getGenerator()
    {
        $generator = new EventGenerator($this->filesystem);
        $generator->setSkeletonDirs([__DIR__.'/../../Resources/skeleton']);

        return $generator;
    }

    protected function getBundle()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue($this->tmpDir));
        $bundle->expects($this->any())->method('getName')->will($this->returnValue('FooBarBundle'));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        return $bundle;
    }
}
