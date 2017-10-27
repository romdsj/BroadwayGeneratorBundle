<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator;

use Broadway\CommandHandling\CommandHandler;

class CommandHandlerManipulator extends BroadwayPhpManipulator
{
    public function __construct(CommandHandler $commandHandler)
    {
        parent::__construct($commandHandler);
    }

    protected function getObjectType()
    {
        return 'Command Handler';
    }

    protected function getObjectHandleType()
    {
        return 'Command';
    }

    protected function getMethodName()
    {
        return 'handle';
    }
}