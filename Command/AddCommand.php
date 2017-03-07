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
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageIndexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add command.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class AddCommand extends Command
{
    private $indexer;

    public function __construct(PageIndexer $indexer)
    {
        $this->indexer = $indexer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-page:add')
            ->setDescription('Index page document.')
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

        $storage = $this->indexer->getStorage();

        $output->writeln('Indexer: '.get_class($this->indexer));
        $output->writeln('  Storage: '.get_class($storage));
        $output->writeln('    DSN: '.$storage->getConnectionString());

        $identity = new DocumentIdentity("page_{$treeId}_{$language}");

        if (!$this->indexer->add($identity)) {
            $output->writeln("<error>Document {$identity->getIdentifier()} could not be loaded.</error>");

            return 1;
        }

        $output->writeln("{$identity->getIdentifier()} index done.");

        return 0;
    }
}
