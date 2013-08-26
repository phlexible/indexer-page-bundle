<?php

/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * Indexer Element Tools
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_IndexerElements_Tools
{
    /**
     * @var MWF_Core_Queue_Manager
     */
    protected $_queueManager;

    /**
     * @var MWF_Core_Indexer_Tools
     */
    protected $_indexerTools;

    /**
     * @var Makeweb_IndexerElements_Indexer
     */
    protected $_indexer;

    /**
     * Constructor
     *
     * @param MWF_Core_Queue_Manager          $queueManager
     * @param MWF_Core_Indexer_Tools          $indexerTools
     * @param Makeweb_IndexerElements_Indexer $indexer
     */
    public function __construct(MWF_Core_Queue_Manager          $queueManager,
                                MWF_Core_Indexer_Tools          $indexerTools,
                                Makeweb_IndexerElements_Indexer $indexer)
    {
        $this->_queueManager = $queueManager;
        $this->_indexer      = $indexer;
        $this->_indexerTools = $indexerTools;
    }

    /**
     * Schedule document update.
     *
     * @param Makeweb_Elements_Tree_Node $node
     * @param array                      $languages (optional)
     */
    public function queueUpdate(Makeweb_Elements_Tree_Node $node, array $languages = array())
    {
        $tid = $node->getId();

        if (!count($languages))
        {
            $languages = $node->getOnlineLanguages();
        }

        foreach ($languages as $language)
        {
            $identifier = 'treenode_' . $tid . '_' . $language;

            try
            {
                if (!$this->_indexer->isIndexibleNode($node, $language))
                {
                    $this->remove($node, array($language));
                }

                $job = new MWF_Core_Indexer_Job_AddNode();
                $job->setIdentifier($identifier);
                $job->setStorageIds(array_keys($this->getRepositories()));
                $job->setIndexerId('elements');

                $this->_queueManager->addUniqueJob($job, MWF_Core_Queue_Manager::PRIORITY_LOW);
            }
            catch (Exception $e)
            {
                MWF_Log::exception($e);
            }
        }
    }

    /**
     * Get accepted repositories for element indexer.
     */
    public function getRepositories()
    {
        $repositories = $this->_indexerTools->getRepositoriesByAcceptedStorage(
            $this->_indexer->getDocumentType()
        );

        return $repositories;
    }

    /**
     * Remove documents from reposuitory for current node.
     *
     * @param Makeweb_Elements_Tree_Node $node
     * @param array                      $languages (optional)
     */
    public function remove(Makeweb_Elements_Tree_Node $node, array $languages = array())
    {
        $tid = $node->getId();

        if (!count($languages))
        {
            $languages = $node->getOnlineLanguages();
        }

        foreach ($languages as $language)
        {
            $identifier = 'treenode_' . $tid . '_' . $language;

            $repositories = $this->getRepositories();
            foreach ($repositories as $repository)
            {
                try
                {
                    $repository->removeByIdentifier($identifier);
                }
                catch (exception $e)
                {
                    MWF_Log::exception($e);
                }
            }
        }
    }

}
