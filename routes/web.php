<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
/** @OA\Info(title="My First API", version="0.1") */
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('/trips', ['uses' => 'FlightController@getTrips'] );
    $router->get('/countires', ['uses' => 'FlightController@getCountires'] );
    $router->get('/airports', ['uses' => 'FlightController@getAirports'] );
});
