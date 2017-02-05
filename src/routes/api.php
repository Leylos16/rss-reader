<?php

require_once __DIR__ . '/../ApiCaller.php';

$app->group('/api', function() use ($app) {
    
    // Get categories of a user
    $app->get('/categories/{idUser}/{token}', ApiController::class . ':getCategoriesByUser')->setName('apiCategories');
    
    // Get categories of a user
    $app->get('/feeds/{idUser}/{token}', ApiController::class . ':getFeedsByUser')->setName('apiFeeds');
    
    // Get news of feed by id
    $app->get('/feed/{id}/{token}', ApiController::class . ':getOnlineContentFeedById')->setName('apiGetOnlineFeedById');
    
    // Add feed by user
    $app->post('/feed/{idUser}/{token}', ApiController::class . ':addFeedByUser')->setName('apiAddFeedByUser');
})
->add(function($request, $response, $next){
    if ($request->getAttributes()['route']->getArguments()['token'] !== $this->get('settings')['apiToken']) {
        return $response->withJson('Forbidden', 403);
    }

    $response = $next($request, $response);

    return $response;
});