<?php
class ApiController
{
    protected $container;

    // constructor receives container instance
    public function __construct(\Slim\Container $container) {
        $this->container = $container;
    }
    
    public function getCategoriesByUser($request, $response)
    {
        $categories = [];
        
        try {
            $rows = $this->container['db']
                ->table('category')
                ->where([ 'id_user' => $request->getAttribute('idUser') ])
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
    
    public function getFeedsByUser($request, $response)
    {
        $feeds = [];
        
        try {
            $rows = $this->container['db']
                ->table('feed')
                ->where([ 'user_id' => $request->getAttribute('idUser') ])
                ->get();
            foreach ($rows as $key => $category) {
                $feeds[$key]['id'] = $category->id;
                $feeds[$key]['title'] = $category->title;    
            }
        } catch (Exception $ex) {
            return $response->withJson('Error' , 500);
        }
        
        return $response->withJson($feeds, 200);
    }
    
    public function addFeedByUser($request, $response)
    {        //var_dump($request->getParams()); die;

        $params = $request->getParams();
        $params['user_id'] = $request->getAttribute('idUser');
        
        try {
            $this->container['db']->table('feed')->insert($params);
        } catch (Exception $ex) {
            return $response->withJson('Error' . $ex->getMessage() , 500);
        }
        
        return $response->withJson('Ok', 200);
    }
    
    public function getOnlineContentFeedById($request, $response)
    {
        $url = 'https://news.stockway.pro/backend';
        
        $fileRss = new \SimpleXMLElement($url, null, true);
        $items = $fileRss->channel->item;
        
        $limit = isset($limit) ? $limit : count($items);
        
        $news = [];
        
        //veriier accent
        for ($i=0; $i < $limit; $i++) {
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
                'link' => (string) $items[$i]->link
            );
        }
        
        return $response->withJson($news, 200);
    }
}

