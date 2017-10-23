<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Command;

use Broadway\CommandHandling\CommandHandler;
use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Generator\CommandGenerator;
use RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator\CommandHandlerManipulator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GenerateCommandCommand extends GeneratorCommand
{
    const MAX_ATTEMPTS = 5;

    protected function configure()
    {
        $this
            ->setName("rdsj:broadway:generate-command")
            ->setDescription("Generate a Broadway command")
            ->setDefinition(array(
                                new InputArgument('bundle', InputArgument::OPTIONAL, 'The bundle where the command is generated'),
                                new InputArgument('name', InputArgument::OPTIONAL, 'The command\'s name (e.g. app:my-command)'),
                                new InputArgument('command-handler', InputArgument::OPTIONAL, 'The command handler\'s name (e.g. CommandHandler\MyCommandHandler)'),
                            ))
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $bundle = $input->getArgument('bundle');
        $name = $input->getArgument('name');
        $commandHandler = $input->getArgument('command-handler');
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
            $output->writeln(sprintf('Command name: %s', $name));
        } else {
            $output->writeln(array(
                                 '',
                                 'Now, provide the name of the command as you type it in the console',
                                 '(e.g. <comment>app:my-command</comment>)',
                                 '',
                             ));
            $question = new Question($questionHelper->getQuestion('Command name', $name), $name);
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('The command name cannot be empty.');
                }
                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);
            $name = $questionHelper->ask($input, $output, $question);
            $input->setArgument('name', $name);
        }

        // command handler
        if (null !== $commandHandler) {
            $output->writeln(sprintf('CommandHandler name: %s', $commandHandler));
        } else {
            $output->writeln(array(
                                 '',
                                 'Now, provide the service id of the command handler you registered',
                                 '(e.g. <comment>my.commandler_handler</comment>)',
                                 '<info>Let it empty if you don\'t want to add automatically the command handler method to command handler</info>',
                             ));
            $question = new Question($questionHelper->getQuestion('Command handler', $commandHandler), $commandHandler);
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    $answer = null;
                }
                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);
            $commandHandler = $questionHelper->ask($input, $output, $question);
            $input->setArgument('command-handler', $commandHandler);
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
        $commandHandlerName = $input->getArgument('command-handler');
        try {
            $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
        }

        try {
            /** @var CommandHandler $commandHandler */
            $commandHandler = $this->getContainer()->get($commandHandlerName);
        } catch (ServiceNotFoundException $e) {
            $output->writeln(sprintf('<bg=red>Command Handler "%s" does not exist.</>', $commandHandlerName));
            $commandHandler = '';
        }

        $generator = $this->getGenerator($bundle);

        $generator->generate($bundle, $name);

        $output->writeln(sprintf('Generated the <info>%s</info> command in <info>%s</info> and handle it in <info>%s</info>', $name, $bundle->getName(), $commandHandlerName));


        if (!empty($commandHandler)) {
            $this->addMethodToCommandHandler($commandHandler, $bundle, $name);
            $output->writeln(sprintf('Added the handle method of event : <info>%s</info> to command handler <info>%s</info>', $name, $commandHandlerName));
        }


        $questionHelper->writeGeneratorSummary($output, array());
    }

    private function addMethodToCommandHandler(CommandHandler $commandHandler, Bundle $bundle, $methodName)
    {
        $commandHandlerManipulator = new CommandHandlerManipulator($commandHandler, $bundle);

        $commandHandlerManipulator->addHandlerMethod($methodName);
    }

    protected function createGenerator()
    {
        return new CommandGenerator($this->getContainer()->get('filesystem'));
    }
}