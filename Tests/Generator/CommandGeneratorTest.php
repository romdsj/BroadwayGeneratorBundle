<?php

namespace Sensio\Bundle\GeneratorBundle\Tests\Generator;

use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator\CommandGenerator;

class CommandGeneratorTest extends GeneratorTest
{
    public function testGenerateController()
    {
        $commandName = 'FooBar';
        $commandFile = 'Command/FooBarCommand.php';
        $commandPath = $this->tmpDir.'/'.$commandFile;

        $this->getGenerator()->generate($this->getBundle(), $commandName);

        $this->assertTrue(file_exists($commandPath), sprintf('%s file has been generated.', $commandFile));

        $commandContent = file_get_contents($commandPath);
        $strings = array(
            'namespace Foo\\BarBundle\\Command',
            'class FooBarCommand',
            'private $uuid;',
            'public function __construct($uuid)',
            '$this->uuid = $uuid;',
            'public function getUUID()',
            'return $this->uuid;'
        );
        foreach ($strings as $string) {
            $this->assertContains($string, $commandContent);
        }
    }

    protected function getGenerator()
    {
        $generator = new CommandGenerator($this->filesystem);
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
