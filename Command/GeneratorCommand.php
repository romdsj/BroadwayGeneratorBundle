<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Command;


use Symfony\Component\HttpKernel\Bundle\BundleInterface;

abstract class GeneratorCommand extends \Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand
{
    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/BroadwayGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->getContainer()->get('kernel')->getRootdir().'/Resources/BroadwayGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $skeletonDirs[] = __DIR__.'/../Resources/skeleton';
        $skeletonDirs[] = __DIR__.'/../Resources';

        return $skeletonDirs;
    }
}