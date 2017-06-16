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
            ->addArgument('pattern', InputArgument::REQUIRED, 'Regular expression pattern for matching a key')
            ->addArgument('directory', InputArgument::REQUIRED, 'A path to YAML file')
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

        $pattern = '/' . $input->getArgument('pattern') . '/';
        $finder = new Finder;
        $finder->name('/\.(' . implode('|', array_map('preg_quote', $input->getOption('extension'))) . ')$/');

        $matches = [];

        foreach ($finder->in($input->getArgument('directory')) as $file) {
            try {
                $data = Yaml::parse(file_get_contents($file->getRealPath()));

                if (is_array($data)) {
                    foreach ($data as $key => $value) {
                        if (preg_match($pattern, $key)) {
                            $this->getLogger($output)
                                ->debug('Match for ' . $key . ' found: ' . $file->getRealPath());

                            yield [$file->getRealPath(), $key, $value];
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
}
