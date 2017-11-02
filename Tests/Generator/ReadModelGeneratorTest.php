<?php

namespace Sensio\Bundle\GeneratorBundle\Tests\Generator;

use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator\ReadModelGenerator;

class ReadModelGeneratorTest extends GeneratorTest
{
    public function testGenerateController()
    {
        $commandName = 'FooBar';
        $commandFile = 'ReadModel/FooBarReadModel.php';
        $commandPath = $this->tmpDir.'/'.$commandFile;

        $this->getGenerator()->generate($this->getBundle(), $commandName);

        $this->assertTrue(file_exists($commandPath), sprintf('%s file has been generated.', $commandFile));

        $commandContent = file_get_contents($commandPath);
        $strings = array(
            'namespace Foo\\BarBundle\\ReadModel',
            'use Broadway\ReadModel\Identifiable;',
            'class FooBarReadModel implements Identifiable',
            'private $id;',
            'public function __construct($id)',
            '$this->id = $id;',
            'public function getId()',
            'return $this->id;'
        );
        foreach ($strings as $string) {
            $this->assertContains($string, $commandContent);
        }
    }

    protected function getGenerator()
    {
        $generator = new ReadModelGenerator($this->filesystem);
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
