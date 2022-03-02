<?php
namespace IW\Myhe\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('validate')
            ->setDescription('Finds all YAMLs in directory and warns about syntax error');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        iterator_to_array($this->find($input, $output));

        return self::SUCCESS;
    }
}
