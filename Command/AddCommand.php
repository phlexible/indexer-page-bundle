<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Command;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add command
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class AddCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-element:add')
            ->setDescription('Index element document.')
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

        $output->writeln('Indexer: ' . get_class($indexer));
        $output->writeln('  Storage: ' . get_class($storage));
        $output->writeln('    DSN: ' . $storage->getConnectionString());

        $identity = new DocumentIdentity("element_{$treeId}_{$language}");

        if (!$indexer->add($identity)) {
            $output->writeln("<error>Document {$identity->getIdentifier()} could not be loaded.</error>");

            return 1;
        }

        $output->writeln("{$identity->getIdentifier()} index done.");

        return 0;
    }

}
