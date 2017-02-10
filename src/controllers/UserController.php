<?php
/**
 * Controller which contains methods user login
 */
class UserController
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
     * Page login
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function login($request, $response)
    {
        if ($request->isPost()) {
            $username = $request->getParam('username');
            $password = $request->getParam('password');

            $credentials = [ 'username' => $username, 'password' => $password ];
            
            if (empty($username) || empty($password)) {
                $params['error'] = 2;

                return $this->container->renderer->render($response, 'login.phtml', [ 'params' => $params ]);
            }
            
            try {
                $builder = $this->container['db']->table('user');
            
                $user = $builder
                        ->select()
                        ->where($credentials)
                        ->first();

                if (!$user) {
                    $isExistUsername = $builder
                        ->select()
                        ->where([ 'username' => $username ])
                        ->first();
                    
                    if ($isExistUsername) {
                        $params['error'] = 1;

                        return $this->container->renderer->render($response, 'login.phtml', [ 'params' => $params ]);
                    } else {
                        $idUser = $builder->insertGetId($credentials);
                        $user = $builder->find($idUser);
                    }
                }

                $_SESSION['sessionUser'] = serialize($user);

                return $response->withRedirect(
                    $this->container->router->pathFor('home')
                );
            } catch (Exception $ex) {
                $params['error'] = 0;

                return $this->container->renderer->render($response, 'login.phtml', [ 'params' => $params ]);
            }
            
        }

        return $this->container->renderer->render($response, 'login.phtml');
    }
    
    /**
     * Logout action
     * 
     * @param $request Slim\Http\Request
     * @param $response Slim\Http\Response
     * 
     * @return Slim\Http\Response
     */
    public function logout($request, $response)
    {
        session_destroy();
    
        return $response->withRedirect(
            $this->container->router->pathFor('login')
        );
    }
            
}

