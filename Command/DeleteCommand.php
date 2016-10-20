<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Command;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete command.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class DeleteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-element:delete')
            ->setDescription('Delete element document.')
            ->addArgument('treeId', InputArgument::REQUIRED, 'Tree node ID')
            ->addArgument('language', InputArgument::REQUIRED, 'Language')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);

        $treeId = $input->getArgument('treeId');
        $language = $input->getArgument('language');

        $indexer = $this->getContainer()->get('phlexible_indexer_element.element_indexer');
        $storage = $indexer->getStorage();

        $output->writeln('Indexer: '.get_class($indexer));
        $output->writeln('  Storage: '.get_class($storage));
        $output->writeln('    DSN: '.$storage->getConnectionString());

        $identity = new DocumentIdentity("element_{$treeId}_{$language}");

        $cnt = $storage->delete($identity);

        $output->writeln("Deleted $cnt documents.");

        return 0;
    }
}
