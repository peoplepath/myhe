<?php
namespace IW\Myhe\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('show')
            ->setDescription('Show all values for each key')
            ->addOption(
                'match',
                'm',
                InputOption::VALUE_REQUIRED,
                'Regex matching scalar value'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // aggregate by keys
        $keys = [];
        foreach ($this->find($input, $output) as list($filename, $key, $match)) {
            $value = $this->pickValue($key, $match);
            if ($pattern = $input->getOption('match')) {
                if (!is_scalar($value) || !preg_match('/' . $pattern . '/', $value)) {
                    continue;
                }
            }


            $keys[implode('.', $key)][$filename] = $value;
        }

        // show result
        $io = new SymfonyStyle($input, $output);
        if ($keys) {
            foreach ($keys as $key => $values) {
                $io->section($key);
                foreach ($values as $filename => $value) {
                    $output->writeln(sprintf('<options=bold,underscore>%s</>', $filename));
                    $io->text(print_r($value, true));
                }
            }
        } else {
            $io->caution('No keys found');
        }

        return self::SUCCESS;
    }

    private function pickValue(array $keys, $match)
    {
        while (($key = array_shift($keys)) && is_array($match) && array_key_exists($key, $match)) {
            $match = $match[$key];
        }

        return $match;
    }
}
