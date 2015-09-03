<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Template\TemplateInterface;
use Zend\Stratigility\MiddlewareInterface;

/**
 * Action abstract class
 */
abstract class AbstractAction implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    );

    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Get services
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get template renderer
     *
     * @return TemplateInterface
     */
    public function getRenderer()
    {
        return $this->getContainer()->get('Zend\Expressive\Template\TemplateInterface');
    }
}
