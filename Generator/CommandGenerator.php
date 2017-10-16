<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class CommandGenerator extends \Sensio\Bundle\GeneratorBundle\Generator\CommandGenerator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(BundleInterface $bundle, $name, $commandHandler = null)
    {
        $bundleDir = $bundle->getPath();
        $commandDir = $bundleDir.'/Command';
        self::mkdir($commandDir);

        $commandClassName = $this->classify($name).'Command';
        $commandFile = $commandDir.'/'.$commandClassName.'.php';
        if ($this->filesystem->exists($commandFile)) {
            throw new \RuntimeException(sprintf('Command "%s" already exists', $name));
        }

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'class_name' => $commandClassName,
            'name' => $name,
        );

        $this->renderFile('command/Command.php.twig', $commandFile, $parameters);
    }
}