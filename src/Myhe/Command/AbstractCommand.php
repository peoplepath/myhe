<?php
namespace IW\Myhe\Command;

use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractCommand extends Command
{
    private $logger;

    protected function configure()
    {
        $this
        // TODO [Ondrej Esler, A] as option
            ->addArgument('directory', InputArgument::REQUIRED, 'A path to YAML files')
            ->addOption(
                'pattern', 'p',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Regular expression pattern for matching a key. Use multiple patterns for each level of nesting'
            )
            ->addOption(
                'extension', 'e',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'File extension',
                ['yml', 'yaml']
            );
    }

    protected function getLogger(OutputInterface $output): ConsoleLogger
    {
        if ($this->logger === null) {
            $this->logger = new ConsoleLogger($output);
        }

        return $this->logger;
    }

    protected function find(InputInterface $input, OutputInterface $output): Generator
    {
        $finder = new Finder;
        $finder->name('/\.(' . implode('|', array_map('preg_quote', $input->getOption('extension'))) . ')$/');

        $matches = [];

        foreach ($finder->in($input->getArgument('directory')) as $file) {
            try {
                $data = Yaml::parse(file_get_contents($file->getRealPath()));

                if (is_array($data)) {
                    foreach ($data as $key => $value) {
                        foreach ($this->matchKeys($key, $value, $input->getOption('pattern')) as $keys) {
                            $this->getLogger($output)
                                ->debug('Match for ' . implode('.', $keys) . ' found: ' . $file->getRealPath());

                            yield [$file->getRealPath(), $keys, $value];
                        }
                    }
                } else {
                    $this->getLogger($output)
                        ->debug('Not an array, skipping: ' . $file->getRealPath());
                }
            } catch (ParseException $exception) {
                $this->getLogger($output)
                    ->warning('Parse error, skipping: ' . $file->getRealPath(), ['exception' => $exception]);
            }
        }
    }


    private function matchKeys(string $key, $value, array $patterns, array $matches=[]): Generator {
        if ($patterns) {
            if (preg_match('/' . array_shift($patterns) . '/', $key)) {
                $matches[] = $key;

                if ($patterns && is_array($value)) {
                    foreach ($value as $k => $v) {
                        foreach ($this->matchKeys($k, $v, $patterns, $matches) as $m) {
                            yield $m;
                        }
                    }
                } else {
                    yield $matches;
                }
            }
        } else {
            $matches[] = $key;
            yield $matches;
        }
    }
}
