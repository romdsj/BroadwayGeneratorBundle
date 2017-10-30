<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Command;

use Broadway\ReadModel\Projector;
use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator\EventGenerator;
use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator\ProjectorManipulator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class GenerateEventCommand extends GeneratorCommand
{
    const MAX_ATTEMPTS = 5;

    protected function configure()
    {
        $this
            ->setName("rdsj:broadway:generate-event")
            ->setDescription("Generate a Broadway event")
            ->setDefinition(array(
                new InputArgument('bundle', InputArgument::OPTIONAL, 'The bundle where the event is generated'),
                new InputArgument('name', InputArgument::OPTIONAL, 'The event\'s name'),
                new InputArgument('projector', InputArgument::OPTIONAL, 'The projector\'s name (e.g. my.projector)'),
            ))
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $bundle = $input->getArgument('bundle');
        $name = $input->getArgument('name');
        $commandHandler = $input->getArgument('projector');
        if (null !== $bundle && null !== $name && null !== $commandHandler) {
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
                                 'First, you need to give the name of the bundle where the command will',
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
        // command name
        if (null !== $name) {
            $output->writeln(sprintf('Event name: %s', $name));
        } else {
            $output->writeln(array(
                                 '',
                                 'Now, provide the name of the event you want',
                                 '',
                             ));
            $question = new Question($questionHelper->getQuestion('Event name', $name), $name);
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('The event name cannot be empty.');
                }
                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);
            $name = $questionHelper->ask($input, $output, $question);
            $input->setArgument('name', $name);
        }

        // command handler
        if (null !== $commandHandler) {
            $output->writeln(sprintf('Projector name: %s', $commandHandler));
        } else {
            $output->writeln(array(
                                 '',
                                 'Now, provide the service id of the projector you registered',
                                 '(e.g. <comment>my.projector</comment>)',
                                 '<info>Let it empty if you don\'t want to add automatically the event method to projector</info>',
                             ));
            $question = new Question($questionHelper->getQuestion('Projector', $commandHandler), $commandHandler);
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    $answer = null;
                }
                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);
            $commandHandler = $questionHelper->ask($input, $output, $question);
            $input->setArgument('projector', $commandHandler);
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
        $projectorName = $input->getArgument('projector');
        try {
            $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
        }

        try {
            /** @var Projector $projector */
            $projector = $this->getContainer()->get($projectorName);
        } catch (ServiceNotFoundException $e) {
            $output->writeln(sprintf('<bg=red>Projector "%s" does not exist.</>', $projectorName));
            $projector = '';
        }

        $generator = $this->getGenerator($bundle);

        $generator->generate($bundle, $name);

        $output->writeln(sprintf('Generated the <info>%s</info> command in <info>%s</info> and handle it in <info>%s</info>', $name, $bundle->getName(), $projectorName));


        if (!empty($projector)) {
            $this->addMethodToProjector($projector, $name);
            $output->writeln(sprintf('Added the handle method of event : <info>%s</info> to projector <info>%s</info>', $name, $projectorName));
        }


        $questionHelper->writeGeneratorSummary($output, array());
    }

    private function addMethodToProjector(Projector $projector, $methodName)
    {
        $commandHandlerManipulator = new ProjectorManipulator($projector);

        $commandHandlerManipulator->addHandlerMethod($methodName);
    }

    protected function createGenerator()
    {
        return new EventGenerator($this->getContainer()->get('filesystem'));
    }
}