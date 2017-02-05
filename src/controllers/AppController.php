<?php
class AppController
{
    protected $container;

    public function __construct(\Slim\Container $container) {
        $this->container = $container;
    }
    
    public function home($request, $response)
    {
        $router = $this->container->router;
        $params = [];
        $params['messages'] = $this->container->flash->getMessages();
        
        $idUser = unserialize($_SESSION['sessionUser'])->id;
        
        try {
            $apiCaller = new ApiCaller(
                $router,
                $this->container->settings['apiToken'],
                $idUser
            );
            $params['categories'] = $apiCaller->sendRequest('GET', 'apiCategories');
            $params['feeds'] = $apiCaller->sendRequest('GET', 'apiFeeds');
        } catch (Exception $ex) {
            $newResponse = $response->withStatus(500);
            $response = $newResponse;
            $params['error'] = true;
        }
        
        // Render index view
        return $this->container->renderer->render(
            $response, 'index.phtml', [ 'params' => $params ]
        );
    }
    
    public function addFeed($request, $response)
    {
        $paramsAddFeed = $request->getParams();
        
        try {
            $fileRss = new \SimpleXMLElement($paramsAddFeed['url'], null, true);
            $paramsAddFeed['title'] = (string)$fileRss->channel->title;
            $idUser = unserialize($_SESSION['sessionUser'])->id;
            
            $apiCaller = new ApiCaller(
                $this->container->router,
                $this->container->settings['apiToken'],
                $idUser
            );
            
            $apiCaller->sendRequest('POST', 'apiAddFeedByUser', $paramsAddFeed);
            
            $this->container->flash
                ->addMessage('success', 'Flux enregistré.');
            
            return $response->withRedirect(
                $this->container->router->pathFor('home')
            );
        } catch (Exception $ex) {
            if ($fileRss) {
                $newResponse = $response->withStatus(500);
                $response = $newResponse;
                $params['error'] = true;
                
                return $response->withRedirect(
                    $this->container->router->pathFor('home')
                );
            } else {
                $this->container->flash
                ->addMessage('danger', 'Pas de flux trouvé pour cette URL. Veuillez rééssayez.');
            }
        }
    }
    
    
}

