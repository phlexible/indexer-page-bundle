<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\ContentRenderer;

use Phlexible\Bundle\ElementRendererBundle\Configurator\Configuration;
use Phlexible\Bundle\ElementRendererBundle\Configurator\ConfiguratorInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;
use Phlexible\BUndle\IndexerPageBundle\Indexer\ParametersGenerator\IndexerParametersGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Templating\EngineInterface;

/**
 * Content renderer.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentRenderer implements ContentRendererInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RequestContext
     */
    private $requestContext;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ConfiguratorInterface
     */
    private $configurator;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IndexerParametersGeneratorInterface
     */
    private $indexerParametersGenerator;

    /**
     * @param ContainerInterface                  $container
     * @param RequestContext                      $requestContext
     * @param RequestStack                        $requestStack
     * @param ConfiguratorInterface               $configurator
     * @param EngineInterface                     $templating
     * @param LoggerInterface                     $logger
     * @param IndexerParametersGeneratorInterface $indexerParametersGenerator
     */
    public function __construct(
        ContainerInterface $container,
        RequestContext $requestContext,
        RequestStack $requestStack,
        ConfiguratorInterface $configurator,
        EngineInterface $templating,
        LoggerInterface $logger,
        IndexerParametersGeneratorInterface $indexerParametersGenerator
    ) {
        $this->container                  = $container;
        $this->requestContext             = $requestContext;
        $this->requestStack               = $requestStack;
        $this->configurator               = $configurator;
        $this->templating                 = $templating;
        $this->logger                     = $logger;
        $this->indexerParametersGenerator = $indexerParametersGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function render(DocumentDescriptor $descriptor)
    {
        $node       = $descriptor->getNode();
        $siteroot   = $descriptor->getSiteroot();
        $language   = $descriptor->getLanguage();
        $parameters = $this->indexerParametersGenerator->createParameters($descriptor);

        try {
            ob_start();

            $request = new Request(
                [],
                [],
                [],
                [],
                [],
                [
                    'SERVER_NAME' => $siteroot->getDefaultUrl()->getHostname(),
                ]
            );
            $this->requestStack->push($request);

            $this->container->enterScope('request');
            $this->container->set('request', $request, 'request');

            $siterootUrl = $siteroot->getDefaultUrl();

            foreach ($parameters as $key => $value) {
                $this->requestContext->setParameter($key, $value);
                $request->attributes->set($key, $value);
            }
            $request->setLocale($language);
            $request->attributes->set('routeDocument', $node);
            $request->attributes->set('contentDocument', $node);
            $request->attributes->set('siterootUrl', $siterootUrl);

            try {
                /* @var $configuration Configuration */
                $configuration = $this->configurator->configure($request, null);
                $data          = $configuration->getVariables();

                $content = $this->templating->render($data['template'], (array) $data);
            } catch (\Exception $e) {
                $this->logger->error("Error while rendering node {$node->getId()}: {$e->getMessage()}");

                $this->container->leaveScope('request');

                return null;
            }

            $this->container->leaveScope('request');

            while (ob_get_level() > 0) {
                $ob = ob_get_clean();
                if ($ob) {
                    $this->logger->warning("From output buffer: $ob");
                }
            }

            return $content;
        } catch (\Exception $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            throw $e;
        }
    }
}
