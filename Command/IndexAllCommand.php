<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Command;

use Phlexible\Bundle\QueueBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index all command
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class IndexAllCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-element:index-all')
            ->setDescription('Index all element documents.')
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Queue updates instead of immediate run.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getOption('queue');

        ini_set('memory_limit', -1);

        $container = $this->getContainer();

        $logger = $container->get('logger');
        $indexer = $container->get('phlexible_indexer_element.indexer');
        $storage = $indexer->getStorage();

        $output->writeln('Indexer: ' . $indexer->getName());
        $output->writeln('  Storage: ' . get_class($storage));
        $output->writeln('    DSN: ' . $storage->getConnectionString());

        $documentIds = $indexer->findIdentifiers();

        if (!count($documentIds)) {
            $output->writeln('Nothing to index.');

            return 0;
        }

        $progress = null;
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $progress = new ProgressBar($output, count($documentIds));
            $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% %message%');
            $progress->start();
        }

        $update = null;
        if (!$queue) {
            $update = $storage->createUpdate();
        }

        foreach ($documentIds as $documentId) {
            $document = $indexer->buildDocument($documentId);

            if (!$document) {
                $logger->error("Document $documentId could not be loaded.");
                continue;
            }

            if ($queue) {
                $job = new Job('indexer-element:index', array('--documentId', $document->getIdentifier()));
                $this->getContainer()->get('phlexible_queue.job_manager')->addJob($job);
            } else {
                $update->addDocument($document);
            }

            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $progress->setMessage($document->getIdentifier());
                $progress->advance();
            }
        }

        if (!$queue) {
            $update->commit();
        }

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $progress->finish();
        }
        $output->writeln('');

        $storage->execute($update);

        return 0;
    }

}
