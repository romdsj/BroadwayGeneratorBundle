<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator;

use Broadway\Domain\DomainMessage;
use Broadway\ReadModel\Projector;

class ProjectorManipulator extends BroadwayPhpManipulator
{
    public function __construct(Projector $projector)
    {
        parent::__construct($projector);
    }

    protected function getObjectHandleType()
    {
        $tmp = explode('\\', DomainMessage::class);
        return end($tmp);
    }

    protected function getMethodName()
    {
        return 'apply';
    }
}