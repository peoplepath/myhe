<?php
namespace IW\Myhe\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class FormatCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('format')
            ->setDescription('Format all matching yamls')
            ->addOption(
                'inline', 'l',
                InputOption::VALUE_REQUIRED,
                'Enable inline dump'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // aggregate by filenames
        $filenames = [];
        foreach ($this->find($input, $output) as list($filename)) {
            $filenames[$filename] = true;
        }

        $io = new SymfonyStyle($input, $output);
        if ($io->confirm(sprintf('Do you really want format %d files?', count($filenames)))) {
            // walk trough files and re-save them
            foreach (array_keys($filenames) as $filename) {
                $data = Yaml::parse(file_get_contents($filename));
                file_put_contents($filename, Yaml::dump($data, $input->getOption('inline') ?? PHP_INT_MAX));
            }
        }

        return self::SUCCESS;
    }
}
