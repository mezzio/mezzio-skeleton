<?php

namespace App\Template;

use Zend\Expressive\Router\RouterInterface;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var \Zend\Expressive\Router\RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $assetsPath;

    private $assetsVersion;

    public function __construct(RouterInterface $router, $assetsPath)
    {
        $this->router = $router;
        $this->assetsPath = $assetsPath;
    }

    public function getName()
    {
        return 'zend-expressive';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('path', array($this, 'renderUri')),
            new \Twig_SimpleFunction('asset', array($this, 'renderAssetUrl')),
        ];
    }

    // {{ path(name, parameters, relative) }}
    public function renderUri($name, $parameters = [], $relative = false)
    {
        return $this->router->generateUri($name, $parameters);
    }

    // {{ asset(path, packageName, absolute = false, version = null) }}
    // @TODO: Add $assetsPaths to enable cdn etc.
    public function renderAssetUrl($path, $packageName = null, $absolute = false, $version = null)
    {
        $assetUrl = $path;

        if ($version) {
            $assetUrl .= '?v=' . $version;
        }

        return $assetUrl;
    }
}
