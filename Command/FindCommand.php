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
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageIndexerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add command.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class FindCommand extends Command
{
    private $indexer;

    public function __construct(PageIndexerInterface $indexer)
    {
        parent::__construct();

        $this->indexer = $indexer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-page:find')
            ->setDescription('Find document in page index.')
            ->addArgument('identifier', InputArgument::REQUIRED, 'Document identifier')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = new DocumentIdentity($input->getArgument('identifier'));

        if ($this->indexer->supports($identifier)) {
            $storage = $this->indexer->getStorage();

            $output->writeln('Indexer: '.get_class($this->indexer));
            $output->writeln('  Storage: '.get_class($storage));
            $output->writeln('    DSN: '.$storage->getConnectionString());

            $document = $this->indexer->find($identifier);
            dump($document);
        }

        return 0;
    }
}
