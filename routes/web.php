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
$router->get('/stats', [ 'uses' => 'PasteController@stats' ]);

# Show
$router->get('/{hash}[/{syntax}]', [ 'uses' => 'PasteController@show' ]);

# Upload
$router->post('/paste', [ 'uses' => 'PasteController@create' ]);
