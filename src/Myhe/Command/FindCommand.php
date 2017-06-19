<?php
namespace IW\Myhe\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FindCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('find')
            ->setDescription('Finds matching keys in found YAMLs');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // aggregate by keys
        $keys = [];
        foreach ($this->find($input, $output) as list($filename, $key)) {
            $keys[implode('.', $key)][] = $filename;
        }

        // show result
        $io = new SymfonyStyle($input, $output);
        foreach ($keys as $key => $filenames) {
            $io->section($key);
            $io->listing($filenames);
        }
    }
}
