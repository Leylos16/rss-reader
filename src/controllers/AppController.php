<?php
/**
 * Controller which contains methods app
 */
class AppController
{
    /**
     * @var \Slim\Container $container 
     */
    protected $container;

    /**
     * Constructor controller slim
     * 
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container) {
        $this->container = $container;
    }
    
    /**
     * Get feeds group by category of a user
     * 
     * @return array
     */
    private function getFeedsGroupByCategory()
    {
        $router = $this->container->router;
        $params = [];
        
        try {
            $apiCaller = new ApiCaller(
                $router,
                $this->container->settings['apiToken'],
                unserialize($_SESSION['sessionUser'])->id
            );
            $params['feeds'] = $apiCaller->sendRequest('GET', 'apiFeedsGroupByCategory');
        } catch(Exception $ex) {
            $params['error'] = true;
        }
        
        return $params;
    }
    
    /**
     * Page Home
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function home($request, $response)
    {
        $params = $this->getFeedsGroupByCategory();
        
        if (isset($params['error'])) {
            $newResponse = $response->withStatus(500);
            $response = $newResponse;  
        }
        
        return $this->container->renderer->render(
            $response, 'app.phtml', [ 'params' => $params ]
        );
    }
    
    /**
     * Page news of a feed
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function getFeedById($request, $response)
    {
        $params = $this->getFeedsGroupByCategory();
        
        if (isset($params['error'])) {
            $newResponse = $response->withStatus(500);
            $response = $newResponse;  
        }
        
        $params['feedId'] = $request->getAttribute('id');
        
        return $this->container->renderer->render(
            $response, 'feed.phtml', [ 'params' => $params ]
        );
    }
    
    /**
     * Page news feeds by category
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function getNewsByCategory($request, $response)
    {
        $params = $this->getFeedsGroupByCategory();
        
        if (isset($params['error'])) {
            $newResponse = $response->withStatus(500);
            $response = $newResponse;  
        }
        
        $params['categoryId'] = $request->getAttribute('id');
        
        return $this->container->renderer->render(
            $response, 'category.phtml', [ 'params' => $params ]
        );
    }
}