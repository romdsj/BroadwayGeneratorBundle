<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Command;

class GenerateCommandCommand extends GeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName("rdsj:brodway:generate-command")
            ->setDescription("Generate a Broadway command")
        ;
    }
}