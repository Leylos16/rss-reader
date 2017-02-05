<?php
class ApiCaller
{
    //some variables for the object
    private $router;
    
    private $token;
    
    private $idUser;
    
    //construct an ApiCaller object
    public function __construct(Slim\Router $router, $token, $idUser)
    {
        $this->router = $router;
        $this->token = $token;
        $this->idUser = $idUser;
    }
     
    //send the request to the API server
    //also encrypts the request, then checks
    //if the results are valid
    public function sendRequest($method, $pathname, array $request_params = [])
    {
        $url = $this->router->pathFor(
            $pathname, [ 'idUser' => $this->idUser, 'token' => $this->token ]
        );
        
        $url = 'http://rss-reader.dev' . $url;
        //initialize and setup the curl handler
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        if ($request_params) {  
            curl_setopt($ch, CURLOPT_POST, count($request_params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
        }
        
        //execute the request
        $result = curl_exec($ch);
        //print_r($result); die;
        $statusCode = curl_getinfo($ch)['http_code']; 
        
        //var_dump(curl_getinfo($ch)); die;
        if ($statusCode == 200) {
            return @json_decode($result, true);
        }
        
        throw new Exception('error');
    }
}

