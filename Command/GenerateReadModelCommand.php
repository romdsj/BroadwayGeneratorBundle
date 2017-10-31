<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Command;

use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator\ReadModelGenerator;
use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator\ServiceConfigurationManipulator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class GenerateReadModelCommand extends GeneratorCommand
{
    const MAX_ATTEMPTS = 5;

    protected function configure()
    {
        $this
            ->setName("rdsj:broadway:generate-readmodel")
            ->setDescription("Generate a Broadway readmodel")
            ->setDefinition(array(
                                new InputArgument('bundle', InputArgument::OPTIONAL, 'The bundle where the readmodel is generated'),
                                new InputArgument('name', InputArgument::OPTIONAL, 'The readmodel\'s name'),
                                new InputArgument('service-filename', InputArgument::OPTIONAL, 'The service filename. It should be in Resources\\config\\ of the bundle you set earlier'),
                            ))
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $bundle = $input->getArgument('bundle');
        $name = $input->getArgument('name');
        $serviceFilename = $input->getArgument('service-filename');
        if (null !== $bundle && null !== $name && null !== $serviceFilename) {
            return;
        }
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the BroadwayBundle command generator');
        // bundle
        if (null !== $bundle) {
            $output->writeln(sprintf('Bundle name: %s', $bundle));
        } else {
            $output->writeln(array(
                                 '',
                                 'First, you need to give the name of the bundle where the readmodel will',
                                 'be generated (e.g. <comment>AppBundle</comment>)',
                                 '',
                             ));
            $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());
            $question = new Question($questionHelper->getQuestion('Bundle name', $bundle), $bundle);
            $question->setAutocompleterValues($bundleNames);
            $question->setValidator(function ($answer) use ($bundleNames) {
                if (!in_array($answer, $bundleNames)) {
                    throw new \RuntimeException(sprintf('Bundle "%s" does not exist.', $answer));
                }
                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);
            $bundle = $questionHelper->ask($input, $output, $question);
            $input->setArgument('bundle', $bundle);
        }
        // readmodel name
        if (null !== $name) {
            $output->writeln(sprintf('Readmodel name: %s', $name));
        } else {
            $output->writeln(array(
                                 '',
                                 'Now, provide the name of the readmodel you want',
                                 '',
                             ));
            $question = new Question($questionHelper->getQuestion('Readmodel name', $name), $name);
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('The readmodel name cannot be empty.');
                }
                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);
            $name = $questionHelper->ask($input, $output, $question);
            $input->setArgument('name', $name);
        }

        // service filename
        if (null !== $serviceFilename) {
            $output->writeln(sprintf('Service filename: %s', $serviceFilename));
        } else {
            $output->writeln(array(
                                 '',
                                 'Now, provide the service filename of the bundle you registered',
                                 '(e.g. <comment>service.xml</comment>)',
                                 '<info>Let it empty if you don\'t want to register automatically your readmodel as a service. Only XMl or YAML file are available</info>',
                             ));
            $question = new Question($questionHelper->getQuestion('Service filename', $serviceFilename), $serviceFilename);
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    $answer = null;
                }
                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);
            $serviceFilename = $questionHelper->ask($input, $output, $question);
            $input->setArgument('service-filename', $serviceFilename);
        }

        // summary and confirmation
        $output->writeln(array(
                             '',
                             $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg-white', true),
                             '',
                             sprintf('You are going to generate a <info>%s</info> command inside <info>%s</info> bundle.', $name, $bundle),
                         ));
        $question = new Question($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
        if (!$questionHelper->ask($input, $output, $question)) {
            $output->writeln('<error>Command aborted</error>');
            return 1;
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $bundle = $input->getArgument('bundle');
        $name = $input->getArgument('name');
        $serviceFilename = $input->getArgument('service-filename');
        try {
            $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
        }

        $generator = $this->getGenerator($bundle);

        $generator->generate($bundle, $name);
        $output->writeln(sprintf('Generated the <info>%s</info> readmodel in <info>%s</info>', $name, $bundle->getName()));

        if ($bundle instanceof Bundle) {
            $serviceFilePath = sprintf('%s/Resources/config/%s', $bundle->getPath(), $serviceFilename);
            if (file_exists($serviceFilePath)) {
                $this->addServiceToConfiguration($serviceFilePath, $bundle, $name);
                $output->writeln(sprintf('Added the readmodel : <info>%s</info> to the service\'s configuration <info>%s</info>', $name, $serviceFilePath));
            } else {
                $output->writeln(sprintf('<bg=red>Service file "%s" does not exist.</>', $serviceFilename));
            }
        }

        $questionHelper->writeGeneratorSummary($output, array());
    }

    private function addServiceToConfiguration($filePath, BundleInterface $bundle, $name)
    {
        $serviceConfigurationManipulator = new ServiceConfigurationManipulator($filePath);

        $serviceConfigurationManipulator->addServiceConfiguration($bundle, $name);
    }

    protected function createGenerator()
    {
        return new ReadModelGenerator($this->getContainer()->get('filesystem'));
    }
}