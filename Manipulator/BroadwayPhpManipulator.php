<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;

abstract class BroadwayPhpManipulator extends Manipulator
{
    protected $object;

    protected $reflected;

    public function __construct($object)
    {
        $this->object = $object;
        $this->reflected = new \ReflectionObject($object);
    }

    public function addHandlerMethod($eventName)
    {
        if (!$this->getFilename()) {
            return false;
        }

        if (method_exists($this->object, sprintf("%s%s", $this->getMethodName(), $eventName))) {
            throw new \RuntimeException(sprintf("Method handle%s is already implemented in %s %s", $this->getObjectType(), $eventName, $this->getFilename()));
        }

        $src = file($this->getFilename());
        $lines = array_slice($src, 0, $this->reflected->getEndLine() - 1);

        $lines = array_merge(
            [implode('', $lines)],
            ["\n"],
            [str_repeat(' ', 4), sprintf('public function %s%s(%s%s $%s)', $this->getMethodName(), $eventName, $eventName, $this->getObjectHandleType(), strtolower($this->getObjectHandleType())), "\n"],
            [str_repeat(' ', 4), "{", "\n"],
            [str_repeat(' ', 8), "// @TODO Insert your code here", "\n"],
            [str_repeat(' ', 4), "}", "\n"],
            ["}",]
        );

        Generator::dump($this->getFilename(), implode('', $lines));
    }

    protected function getObjectType()
    {
        return $this->reflected->getShortName();
    }

    abstract protected function getObjectHandleType();

    abstract protected function getMethodName();

    protected function getFilename()
    {
        return $this->reflected->getFileName();
    }
}