<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class EventGenerator extends \Sensio\Bundle\GeneratorBundle\Generator\CommandGenerator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(BundleInterface $bundle, $name)
    {
        $bundleDir = $bundle->getPath();
        $eventDir = $bundleDir.'/Event';
        self::mkdir($eventDir);

        $eventClassName = $this->classify($name).'Event';
        $eventFile = $eventDir.'/'.$eventClassName.'.php';
        if ($this->filesystem->exists($eventFile)) {
            throw new \RuntimeException(sprintf('Event "%s" already exists', $name));
        }

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'class_name' => $eventClassName,
            'name' => $name,
        );

        $this->renderFile('event/Event.php.twig', $eventFile, $parameters);
    }
}