<?php
// Routes

$app->map(['GET', 'POST'], '/login', UserController::class . ':login')->setName('login');

$app->get('/logout', UserController::class . ':logout')->setName('logout');

$checkUser = function ($request, $response, $next) {
    if (!isset($_SESSION['sessionUser'])) {
        return $response->withRedirect($this->get('router')->pathFor('login'));
    }
    
    $response = $next($request, $response);
    
    return $response;
};

$app->get('/', AppController::class . ':home')->setName('home')->add($checkUser);
$app->post('/feed', AppController::class . ':addFeed')->setName('addFeed')->add($checkUser);