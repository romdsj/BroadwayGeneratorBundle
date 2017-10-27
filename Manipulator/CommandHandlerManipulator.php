<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator;

use Broadway\CommandHandling\Command;
use Broadway\CommandHandling\CommandHandler;

class CommandHandlerManipulator extends BroadwayPhpManipulator
{
    public function __construct(CommandHandler $commandHandler)
    {
        parent::__construct($commandHandler);
    }

    protected function getObjectHandleType()
    {
        $tmp = explode('\\', Command::class);
        return end($tmp);
    }

    protected function getMethodName()
    {
        return 'handle';
    }
}