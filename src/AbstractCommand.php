<?php
declare(strict_types=1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command
{
    protected SymfonyStyle|null $io = null;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }
}
