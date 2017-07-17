<?php
namespace App;

use JSoumelidis\ZendSmSfDiBridge\ZendSmSfDiBridge;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class SfContainerConfig
{
    const ENABLE_CACHE = 'sf-container.enable_cache';
    
    const CACHE_FILE = 'sf-container.cache_file';
    
    const CACHE_CLASS = 'sf-container.cache_class';
    
    const CACHE_CLASS_NS = 'sf-container.cache_class_ns';
    
    const CACHE_BASE_CLASS = 'sf-container.cache_base_class';
    
    /**
     * @var array
     */
    protected $config;
    
    /**
     * @var bool
     */
    protected $enableCache = false;
    
    /**
     * @var string
     */
    protected $cacheFile = 'data/sf-container-cache.php';
    
    /**
     * @var string
     */
    protected $cacheClass = 'SfCachedContainer';
    
    /**
     * @var string
     */
    protected $cacheClassNs = '';
    
    /**
     * @var string
     */
    protected $cacheBaseClass = Container::class;
    
    /**
     * SfContainerConfig constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        
        $this->enableCache = (isset($config[static::ENABLE_CACHE]) && $config[static::ENABLE_CACHE]);
        
        if ( isset($config[static::CACHE_FILE]) ) {
            $this->cacheFile = $config[static::CACHE_FILE];
        }
        
        if ( isset($config[static::CACHE_CLASS]) ) {
            $this->cacheClass = $config[static::CACHE_CLASS];
        }
    
        if ( isset($config[static::CACHE_CLASS_NS]) ) {
            $this->cacheClassNs = $config[static::CACHE_CLASS_NS];
        }
    
        if ( isset($config[static::CACHE_BASE_CLASS]) ) {
            $this->cacheBaseClass = $config[static::CACHE_BASE_CLASS];
        }
    }
    
    /**
     * @return Container
     */
    public function create()
    {
        if ( !$this->enableCache || !is_readable($this->cacheFile) )
        {
            $container = $this->build();
            
            if ( $this->enableCache )
            {
                $container->compile();
                
                $dumper = new PhpDumper($container);
                
                file_put_contents($this->cacheFile, $dumper->dump([
                    'class' => $this->cacheClass,
                    'namespace' => $this->cacheClassNs,
                    'base_class' => $this->cacheBaseClass,
                    'debug' => isset($this->config['debug']) && $this->config['debug'],
                ]));
            }
    
            return $container;
        }
        
        return $this->loadFromCache();
    }
    
    /**
     * @return ContainerBuilder
     */
    protected function build()
    {
        $config = $this->config;
        
        $dependencies = isset($config['dependencies']) ? $this->config['dependencies'] : [];
        unset($config['dependencies']);
        
        $container = (new ZendSmSfDiBridge())->toSfDI(
            $dependencies,
            new ContainerBuilder(new ParameterBag($config))
        );
        
        //Inject any additional symfony di definitions provided
        if ( !empty($dependencies['definitions']) && is_array($dependencies['definitions']) ) {
            $container->addDefinitions($dependencies['definitions']);
        }
        
        return $container;
    }
    
    /**
     * @return Container
     */
    protected function loadFromCache()
    {
        require_once($this->cacheFile);
        
        $class = !empty($this->cacheClassNs) ?
            $this->cacheClassNs . '\\' . $this->cacheClass :
            $this->cacheClass;
        
        return new $class;
    }
}