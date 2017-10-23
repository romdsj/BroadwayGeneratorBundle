<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator;

use Broadway\CommandHandling\CommandHandler;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CommandHandlerManipulator extends Manipulator
{
    protected $commandHandler;

    protected $bundle;

    protected $reflected;

    public function __construct(CommandHandler $commandHandler, Bundle $bundle)
    {
        $this->commandHandler = $commandHandler;
        $this->bundle = $bundle;
        $this->reflected = new \ReflectionObject($commandHandler);
    }

    public function addHandlerMethod($eventName)
    {
        if (!$this->getFilename()) {
            return false;
        }

        if (method_exists($this->commandHandler, sprintf("handle%s", $eventName))) {
            throw new \RuntimeException(sprintf("Method %s is already implemented in Command Handler %s", $eventName, $this->getFilename()));
        }

        $src = file($this->getFilename());
        $lines = array_slice($src, 0, $this->reflected->getEndLine() - 1);

        $lines = array_merge(
            [implode('', $lines)],
            ["\n"],
            [str_repeat(' ', 4), sprintf('public function handle%s(%sCommand $command)', $eventName, $eventName), "\n"],
            [str_repeat(' ', 4), "{", "\n"],
            [str_repeat(' ', 8), "// @TODO Insert your code here", "\n"],
            [str_repeat(' ', 4), "}", "\n"],
            ["}",]
        );

        Generator::dump($this->getFilename(), implode('', $lines));
    }

    public function getFilename()
    {
        return $this->reflected->getFileName();
    }
}