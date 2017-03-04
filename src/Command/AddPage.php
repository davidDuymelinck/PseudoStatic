<?php

namespace PseudoStatic\Command;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class AddPage extends Command
{
    protected $projectRoot;

    public function __construct($projectRoot) {
        parent::__construct(null);

        $this->projectRoot = $projectRoot;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('create:page')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new page.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('An easy way to create a page');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = new Local($this->projectRoot.'/site');
        $filesystem = new Filesystem($adapter);
        $helper = $this->getHelper('question');

        $question = new Question('Please enter the url of the page: ', 'no-name');

        $url = $helper->ask($input, $output, $question);

        $filesystem->write($url.'/html.twig', '');

        $question = new ConfirmationQuestion('Do you want a data file for the page? [N/y] ', false);

        if ($helper->ask($input, $output, $question)) {
            $filesystem->write($url.'/data.yaml', '');

            $output->writeln('The page files are created.');
        } else {
            $output->writeln('The page file is created.');
        }
    }
}