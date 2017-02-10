<?php
/**
 *  Controller which contains methods api
 */
class ApiController
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
     * Get categories of a user
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function getCategoriesByUser($request, $response)
    {
        $categories = [];
        
        try {
            $rows = $this->container['db']
                ->table('category')
                ->where([ 'user_id' => $request->getAttribute('idUser') ])
                ->get();
            foreach ($rows as $key => $category) {
                $categories[$key]['id'] = $category->id;
                $categories[$key]['label'] = $category->label;    
            }
        } catch (Exception $ex) {
            return $response->withJson('Error' , 500);
        }
        
        return $response->withJson($categories, 200);
    }
    
   /**
     * Get feeds of a user
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function getFeedsByUser($request, $response)
    {
        $feeds = [];
        
        try {
            $rows = $this->container['db']
                ->table('feed')
                ->select(['feed.id','feed.url_id', 'url.url', 'url.title'])
                ->join('url', 'url.id', '=', 'feed.url_id')
                ->where([ 'feed.user_id' => $request->getAttribute('idUser') ])
                ->get();
            
            foreach ($rows as $key => $new) {
                $feeds[$key]['id'] = $new->id;
                $feeds[$key]['url_id'] = $new->url_id;
                $feeds[$key]['url'] = $new->url;
                $feeds[$key]['title'] = $new->title;    
            }
        } catch (Exception $ex) {
            return $response->withJson('Error' , 500);
        }
        
        return $response->withJson($feeds, 200);
    }
    
    /**
     * Get feeds of a user by category
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function getFeedsGroupByCatByUser($request, $response)
    {
        $feeds = [];
        
        try {
            $rows = $this->container['db']
                ->table('feed')
                ->select(['feed.id', 'url.url', 'url.title', 'feed.category_id', 'category.label'])
                ->join('url', 'url.id', '=', 'feed.url_id')
                ->leftJoin('category', 'category.id', '=', 'feed.category_id')
                ->where([ 'feed.user_id' => $request->getAttribute('idUser') ])
                ->orderBy('feed.category_id', 'DESC')
                ->get();
            foreach ($rows as $key => $new) {
                $categoryId = empty($new->category_id) ? 0 : $new->category_id;
                $categoryLabel = empty($new->label) ? 'Sans catégorie' : $new->label;
                $feeds[$categoryId]['label'] = $categoryLabel;
                $feeds[$categoryId]['feeds'][$key]['id'] = $new->id;
                $feeds[$categoryId]['feeds'][$key]['url'] = $new->url;
                $feeds[$categoryId]['feeds'][$key]['title'] = $new->title;    
            }
        } catch (Exception $ex) {
            return $response->withJson('Error' , 500);
        }
        
        return $response->withJson($feeds, 200);
    }
    
    /**
     * Add a category for a user
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function addCategoryByUser($request, $response)
    {
        $params = $request->getParams();
        $params['user_id'] = $request->getAttribute('idUser');
        
        if (empty($params['label'])) {
            return $response->withJson('Bad request', 400);
        }
        
        try {
            $category = $this->container['db']
                ->table('category')
                ->select()
                ->where([ 
                    'label' => $params['label']
                ])
                ->first();
        
            if($category) {
                $catId = $category->id;
            } else {
                $catId = $this->container['db']->table('category')->insertGetId($params);
            }
        } catch (Exception $ex) {
            return $response->withJson('Error' , 500);       
        }
        
        return $response->withJson($catId, 200);
    }
    
    /**
     * Add a feed rss in a category for a user and store result xml in a file
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function addFeedByUser($request, $response)
    {
        $params = $request->getParams();
        $params['user_id'] = $request->getAttribute('idUser');
        
        if (empty($params['url'])) {
            return $response->withJson('Bad request', 400);
        }
        
        if (!$params['category_id']) {
            $params['category_id'] = null;
        } else { //verify if category exist or belong to user
            $category = $this->container['db']
                ->table('category')
                ->select()
                ->where([ 
                    'id' => $params['category_id'],
                    'user_id' => $params['user_id']
                ])
                ->first();
            
            if (!$category) {
                return $response->withJson('Unauthorized', 401);
            }
        }
        
        try {
            $url = $this->container['db']
                ->table('url')
                ->select()
                ->where([ 
                    'url' => $params['url']
                ])
                ->first();
            
            if ($url) {
                $urlId = $url->id;
            } else {
                $fileRss = new \SimpleXMLElement($params['url'], null, true);
                $urlId = $this->container['db']->table('url')->insertGetId([
                    'url' => $params['url'],
                    'title' => (string)$fileRss->channel->title
                ]);
                $contentRss = file_get_contents($params['url']);
                file_put_contents(__DIR__ . '/../../datas/url-' . $urlId . '.xml', $contentRss);
            }
            
            $feed = $this->container['db']
                ->table('feed')
                ->select()
                ->where([ 
                    'url_id' => $urlId,
                    'user_id' => $params['user_id']
                ])
                ->first();
            
            if ($feed) {
                $feedId = $feed->id;
            } else {
                $feedId = $this->container['db']->table('feed')->insertGetId([
                    'url_id' => $urlId,
                    'category_id' => $params['category_id'] ,
                    'user_id' => $params['user_id']
                ]);
            }
            
            $res = $this->container->router->pathFor('getFeedById', [ 'id' => $feedId ]);
        } catch (Exception $ex) {
            return $response->withJson('Error' , 500);
        }
        
        return $response->withJson($res, 200);
    }
    
    /**
     * Get news of a feed user from stored xml file
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function getContentFeedById($request, $response)
    {
        $feed = $this->container['db']
            ->table('feed')
            ->find($request->getAttribute('id'));
        
        if (!$feed){
            return $response->withJson('Feed not found', 404);
        }
        
        $fileRss = simplexml_load_file(__DIR__ . '/../../datas/url-' . $feed->url_id .'.xml');
        $items = $fileRss->channel->item;
        $length = count($items);
        
        $news = [];
        
        for ($i=0; $i < $length; $i++) {
            if ($items[$i]->pubDate) {
                $pubDate = new \DateTime($items[$i]->pubDate);    
                $pubDate = $pubDate->format('d/m/Y H:i');
            } else {
                $pubDate = null;
            }
            
            $news[] = array(
                'title' => trim(
                    htmlspecialchars(strip_tags((string) $items[$i]->title), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                ),
                'description' => trim(
                    htmlspecialchars(
                        strip_tags((string) $items[$i]->description),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    )
                ),
                'date' => $pubDate, 
                'link' => (string) $items[$i]->link
            );
        }
        
        return $response->withJson($news, 200);
    }
    
    /**
     * Get last news of a user
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function getLastNews($request, $response)
    {
        $router = $this->container->router;
        
        $idUser = $request->getAttribute('idUser');
        
        $apiCaller = new ApiCaller(
                $router,
                $this->container->settings['apiToken'],
                $idUser
            );
        
        if (isset($request->getParams()['category'])) {
            $allFeeds = $apiCaller->sendRequest('GET', 'apiFeedsGroupByCategory');
            $allFeeds = $allFeeds[$request->getParam('category')]['feeds'];
        } else {
            $allFeeds = $apiCaller->sendRequest('GET', 'apiFeeds');
        }
        
        $all = [];
        $i = 0;
        foreach ($allFeeds as $f) {
            $allNews = $apiCaller->sendRequest(
                'GET',
                'apiGetFeedById',
                [],
                [ 'id' => $f['id'] ]
            );
            
            foreach ($allNews as $new) {
                $all[$i] = $new;
                $all[$i]['feed'] = $f['title'];
                $i++;
            }

        }

        function cmp($a, $b) {
            $dateA = DateTime::createFromFormat('d/m/Y H:i', $a['date']);
            $dateB = DateTime::createFromFormat('d/m/Y H:i', $b['date']);

            return ($dateA > $dateB) ? -1 : 1;
        }
        
        usort($all, 'cmp');
        $all = array_slice($all, 0, 20);
        
        return $response->withJson($all, 200);
    }
    
    /**
     * Get online news of all feeds for a user
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function getOnlineFeedsByUser($request, $response)
    {
        $router = $this->container->router;
        
        $idUser = $request->getAttribute('idUser');
        
        $apiCaller = new ApiCaller(
                $router,
                $this->container->settings['apiToken'],
                $idUser
            );
        
        $allFeeds = $apiCaller->sendRequest('GET', 'apiFeeds');
        
        foreach ($allFeeds as $feed) {
            $contentRss = file_get_contents($feed['url']);
            
            if ($contentRss) {
                file_put_contents(__DIR__ . '/../../datas/url-' . $feed['url_id'] . '.xml', $contentRss);
            }
        }
        
        return $response->withJson('Ok', 200);
    }
}

