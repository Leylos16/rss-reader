<?php
class UserController
{
    protected $container;

    public function __construct(\Slim\Container $container) {
        $this->container = $container;
    }
    
    public function login($request, $response)
    {
        if ($request->isPost()) {
            $username = $request->getParam('username');
            $password = $request->getParam('password');

            $credentials = [ 'username' => $username, 'password' => $password ];

            $builder = $this->container['db']->table('user');
            
            $user = $builder
                    ->select()
                    ->where($credentials)
                    ->first();
            
            if (!$user) {
                $isExistUsername = $builder->select()->where([ 'username' => $username ])->get();
                
                if ($isExistUsername) {
                    $params['error'] = true;
                    
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
        }

        return $this->container->renderer->render($response, 'login.phtml');
    }
    
    public function logout($request, $response)
    {
        session_destroy();
    
        return $response->withRedirect(
            $this->container->router->pathFor('login')
        );
    }
            
}

