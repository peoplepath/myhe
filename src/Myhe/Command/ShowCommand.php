<?php
namespace IW\Myhe\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('show')
            ->setDescription('Show all values for each key');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // aggregate by keys
        $keys = [];
        foreach ($this->find($input, $output) as list($filename, $key, $match)) {
            $keys[implode('.', $key)][] = $this->pickValue($key, $match);
        }

        // show result
        $io = new SymfonyStyle($input, $output);
        if ($keys) {
            foreach ($keys as $key => $values) {
                $io->section($key);
                $io->listing(
                    array_unique(
                        array_map(
                            function ($value) {
                                return print_r($value, true);
                            },
                            $values
                        ),
                        SORT_REGULAR
                    )
                );
            }
        } else {
            $io->caution('No keys found');
        }
    }

    private function pickValue(array $keys, $match)
    {
        while (($key = array_shift($keys)) && is_array($match) && array_key_exists($key, $match)) {
            $match = $match[$key];
        }

        return $match;
    }
}
