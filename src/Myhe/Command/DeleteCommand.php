<?php
namespace IW\Myhe\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class DeleteCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('delete')
            ->setDescription('Deletes matching keys from found YAMLs');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // aggregate by filenames
        $filenames = [];
        $noKeys    = 0;
        foreach ($this->find($input, $output) as list($filename, $key)) {
            $filenames[$filename][] = $key;
            $noKeys++;
        }

        $io       = new SymfonyStyle($input, $output);
        $question = sprintf('Do you really want delete %d keys from %d files?', $noKeys, count($filenames));

        if ($io->confirm($question)) {
            // walk trough files and delete wanted keys
            foreach ($filenames as $filename => $keys) {
                $data = Yaml::parse(file_get_contents($filename));

                foreach ($keys as $key) {
                    unset($data[$key]);
                }

                file_put_contents($filename, Yaml::dump($data));
            }
        }
    }
}
