<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Tests\Command;

use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Command\GenerateCommandCommand;
use Sensio\Bundle\GeneratorBundle\Tests\Command\GenerateCommandTest;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateCommandCommandTest extends GenerateCommandTest
{
    protected $generator;

    protected $bundle;

    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand($options, $input, $expected)
    {
        list($bundle, $name) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $name)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        return array(
            array(
                array(),
                "FooBarBundle\napp:foo-bar\n",
                array('FooBarBundle', 'app:foo-bar'),
            ),

            array(
                array('bundle' => 'FooBarBundle'),
                "app:foo-bar\n",
                array('FooBarBundle', 'app:foo-bar'),
            ),

            array(
                array('name' => 'app:foo-bar'),
                "FooBarBundle\n",
                array('FooBarBundle', 'app:foo-bar'),
            ),

            array(
                array('bundle' => 'FooBarBundle', 'name' => 'app:foo-bar'),
                '',
                array('FooBarBundle', 'app:foo-bar'),
            ),
        );
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand($options, $expected)
    {
        list($bundle, $name) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $name)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $tester->execute($options, array('interactive' => false));
    }

    public function getNonInteractiveCommandData()
    {
        return array(
            array(
                array('bundle' => 'FooBarBundle', 'name' => 'app:my-command'),
                array('FooBarBundle', 'app:my-command'),
            ),
        );
    }

    protected function getCommand($generator)
    {
        $command = new GenerateCommandCommand();

        $command->setContainer($this->getContainer());
        $helperSet = $this->getHelperSet();
        if ($helperSet) {
            $command->setHelperSet($helperSet);
        }
        $command->setGenerator($generator);

        return $command;
    }

    protected function getGenerator()
    {
        if (null === $this->generator) {
            $this->setGenerator();
        }

        return $this->generator;
    }

    protected function setGenerator()
    {
        // get a noop generator
        $this->generator = $this
            ->getMockBuilder('RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator\CommandGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock()
        ;
    }

    protected function getBundle()
    {
        if (null == $this->bundle) {
            $this->setBundle();
        }

        return $this->bundle;
    }

    protected function setBundle()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue(''));
        $bundle->expects($this->any())->method('getName')->will($this->returnValue('FooBarBundle'));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        $this->bundle = $bundle;
    }
}
