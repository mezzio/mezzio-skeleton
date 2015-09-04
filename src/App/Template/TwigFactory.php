<?php

namespace App\Container;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\Twig;
use Twig_Loader_Filesystem as TwigLoader;
use Twig_Environment as TwigEnvironment;
use Twig_Extension_Debug as TwigExtensionDebug;

class TwigFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        // Create the engine instance
        $loader = new TwigLoader(['templates']);
        $environment = new TwigEnvironment($loader, [
            'cache' => ($config['debug']) ? false : $config['templates']['cache_dir'],
            'debug' => $config['debug'],
            'strict_variables' => $config['debug'],
            'auto_reload' => $config['debug']
        ]);

        // Add extensions
        $environment->addExtension(new TwigExtension($container->get(RouterInterface::class), ''));

        if ($config['debug']) {
            $environment->addExtension(new TwigExtensionDebug());
        }

        // Inject environment
        $twig = new Twig($environment, $config['templates']['extension']);

        // Add template paths
        foreach ($config['templates']['paths'] as $path => $namespace) {
            $twig->addPath($path, $namespace);
        }

        return $twig;
    }
}
