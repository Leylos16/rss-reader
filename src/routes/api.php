<?php
/**
 * Routes for api
 */

$app->group('/api', function() use ($app) {
    
    // Get categories of a user
    $app->get(
        '/categories/{idUser}/{token}',
        ApiController::class . ':getCategoriesByUser'
    )->setName('apiCategories');
    
    // Get feeds of a user
    $app->get(
        '/feeds/{idUser}/{token}',
        ApiController::class . ':getFeedsByUser'
    )->setName('apiFeeds');
    
    // Get feeds group by category of a user
    $app->get(
        '/feeds/categories/{idUser}/{token}',
        ApiController::class . ':getFeedsGroupByCatByUser'
    )->setName('apiFeedsGroupByCategory');
    
    // Get news of feed by id
    $app->get(
        '/feed/{id}/{idUser}/{token}',
        ApiController::class . ':getContentFeedById'
    )->setName('apiGetFeedById');
    
    // Get last news of a user
    $app->get(
        '/last/news/{idUser}/{token}',
        ApiController::class . ':getLastNews'
    )->setName('apiGetLastNews');
    
    // Add category by user
    $app->put(
        '/category/{idUser}/{token}',
        ApiController::class . ':addCategoryByUser'
    )->setName('apiAddCategoryByUser');
    
    // Add feed by user
    $app->put(
        '/feed/{idUser}/{token}',
        ApiController::class . ':addFeedByUser'
    )->setName('apiAddFeedByUser');
    
    // Get online all feeds of a user
    $app->get(
        '/online/feeds/{idUser}/{token}',
        ApiController::class . ':getOnlineFeedsByUser'
    )->setName('apiGetOnlineFeedsByUser');
})
->add(function($request, $response, $next){
    //function to verify token is valid to access api
    if ($request->getAttributes()['route']->getArguments()['token'] !== $this->get('settings')['apiToken']) {
        return $response->withJson('Forbidden', 403);
    }

    $response = $next($request, $response);

    return $response;
});