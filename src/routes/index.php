<?php
/**
 * Routes for app
 */

//Page login
$app->map(['GET', 'POST'], '/login', UserController::class . ':login')->setName('login');

//Logout action
$app->get('/logout', UserController::class . ':logout')->setName('logout');

//Function to verify if user is connected
$checkUser = function ($request, $response, $next) {
    if (!isset($_SESSION['sessionUser'])) {
        return $response->withRedirect($this->get('router')->pathFor('login'));
    }
    
    $response = $next($request, $response);
    
    return $response;
};

//Page home
$app->get(
    '/',
    AppController::class . ':home'
)->setName('home')->add($checkUser);

//Page content feed
$app->get(
    '/feed/{id}',
    AppController::class . ':getFeedById'
)->setName('getFeedById')->add($checkUser);

//Page news feeds by category
$app->get(
    '/category/{id}',
    AppController::class . ':getNewsByCategory'
)->setName('getNewsByCategory')->add($checkUser);