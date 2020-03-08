<?php

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

$router->get('/', [ 'uses' => 'PasteController@index' ]);
$router->get('/stats', [ 'uses' => 'StatsController@index' ]);

# Show
$router->get('/{hash:.*}', [ 'uses' => 'PasteController@show' ]);

# Upload
$router->post('/paste', [ 'uses' => 'PasteController@create' ]);
