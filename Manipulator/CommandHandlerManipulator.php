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
        return end(explode('\\', Command::class));
    }

    protected function getMethodName()
    {
        return 'handle';
    }
}