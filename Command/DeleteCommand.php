<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete command
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

        $output->writeln('Indexer: ' . $indexer->getName());
        $output->writeln('  Storage: ' . get_class($storage));
        $output->writeln('    DSN: ' . $storage->getConnectionString());

        $identifier = "treenode_{$treeId}_{$language}";

        $commands = $storage->createCommands();
        $commands->delete($identifier);

        $storage->runCommands($commands);

        return 0;
    }

}
