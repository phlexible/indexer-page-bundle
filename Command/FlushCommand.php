<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Command;

use Phlexible\Bundle\IndexerElementBundle\Indexer\ElementIndexer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Flush command
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class FlushCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-element:flush')
            ->setDescription('Flush all element documents.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);

        $container = $this->getContainer();

        $indexer = $container->get('phlexible_indexer_element.indexer');

        $output->writeln('Indexer: ' . $indexer->getLabel());

        $storage = $indexer->getStorage();
        $update = $storage->createUpdate();

        $update->addDeleteByType(ElementIndexer::DOCUMENT_TYPE);

        $storage->update($update);

        return 0;
    }

}
