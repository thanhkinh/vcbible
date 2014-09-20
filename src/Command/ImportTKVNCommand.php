<?php

namespace AndyTruong\Bundle\ImportBundle\Command;

use AndyTruong\Bundle\ImportBundle\ImportManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTKVNCommand extends ContainerAwareCommand
{

    private $installer;

    protected function configure()
    {
        $this
            ->setName('vcbible:import')
            ->setDescription('Strart importing')
            ->addOption('restart', null, InputOption::VALUE_OPTIONAL, 'Restart importing process', false)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Restart importing process', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');
        $manager = new ImportManager($this->getContainer());

        if ($restart = (bool) $input->getOption('restart')) {
            $manager->generateQueueItems();
        }
        else {
            for ($i = $limit; $i >= 0; $i--) {
                if ($queue_item = $manager->getQueueItem()) {
                    $output->writeln('Importing ' . $queue_item->getDescription(), OutputInterface::VERBOSITY_NORMAL);
                    $manager->processQueueItem($queue_item);
                }
            }
        }
    }

}