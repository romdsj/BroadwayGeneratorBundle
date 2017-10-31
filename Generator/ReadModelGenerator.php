<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class ReadModelGenerator extends \Sensio\Bundle\GeneratorBundle\Generator\CommandGenerator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(BundleInterface $bundle, $name)
    {
        $bundleDir = $bundle->getPath();
        $readModelDir = $bundleDir.'/ReadModel';
        self::mkdir($readModelDir);

        $readModelClassName = $this->classify($name).'ReadModel';
        $readModelFile = $readModelDir.'/'.$readModelClassName.'.php';
        if ($this->filesystem->exists($readModelFile)) {
            throw new \RuntimeException(sprintf('ReadModel "%s" already exists', $name));
        }

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'class_name' => $readModelClassName,
            'name' => $name,
        );

        $this->renderFile('readmodel/ReadModel.php.twig', $readModelFile, $parameters);
    }
}