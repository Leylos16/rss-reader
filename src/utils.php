<?php
/**
 * Utils to access this methods from template
 */
class Utils {
    
    /**
     * @var \Slim\Container $container 
     */
    private $container;

    /**
     * Constructor
     * 
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container) {
        $this->container = $container;
    }
    
    /**
     * Get path info for a route name
     *
     * @param string $pathname
     * @param array  $extraParams
     * @param string $queryString
     * 
     * @return string
     */
    public function getPathFor($pathname, $extraParams = [], $queryString = [])
    {
        $paramsRequest = [ 
            'idUser' => unserialize($_SESSION['sessionUser'])->id,
            'token' => $this->container->settings['apiToken']
        ];
        
        return $this->container->router->pathFor(
            $pathname,
            array_merge($paramsRequest, $extraParams),
            $queryString
        );
    }
}
