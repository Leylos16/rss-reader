<?php
/**
 * Class to call api from controllers
 */
class ApiCaller
{
    /**
     * @var Slim\Router $router
     */
    private $router;
    
    /**
     * @var string $token
     */
    private $token;
    
    /**
     * @var string $idUser
     */
    private $idUser;
    
    /**
     * Constructor: initialize params of request
     *
     * @param Slim\Router $router
     * @param string $token
     * @param string $idUser
     */
    public function __construct(Slim\Router $router, $token, $idUser)
    {
        $this->router = $router;
        $this->token = $token;
        $this->idUser = $idUser;
    }
     
    /**
     * Send request
     * 
     * @param string $method
     * @param string $pathname
     * @param array  $request_params
     * @param array  $extraRouteParams
     * 
     * @return array
     * 
     * @throws Exception
     */
    public function sendRequest(
        $method, 
        $pathname, 
        array $request_params = [], 
        array $extraRouteParams = []
    ){
        $urlParams = [ 'idUser' => $this->idUser, 'token' => $this->token ];
        
        if ($extraRouteParams) {
            $urlParams = array_merge($urlParams, $extraRouteParams);
        }
        
        $url = $this->router->pathFor($pathname, $urlParams);
        
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $url;
        
        //initialize and setup the curl handler
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if ($request_params) {  
            curl_setopt($ch, CURLOPT_POST, count($request_params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
        }
        
        //execute the request
        $result = curl_exec($ch);
        
        //get status code of response
        $statusCode = curl_getinfo($ch)['http_code']; 
        
        if ($statusCode == 200) {
            return @json_decode($result, true);
        }
        
        throw new Exception('error');
    }
}

