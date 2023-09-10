<?php

/** @var \Laravel\Lumen\Routing\Router $router */
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

Route::group(['prefix' => 'api'], function ($router) {
    // Auth routes
    Route::post('login', 'AuthController@login');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('register', 'AuthController@register');

    // Items routes
    Route::group(['prefix' => 'items'], function ($router) {
        // Listar todos los items
        Route::get('/', 'ItemController@index');
    
        // Obtener un item por ID
        Route::get('/{id}', 'ItemController@show');
    
        // Crear un nuevo item
        Route::post('/', 'ItemController@store');
    
        // Actualizar un item por ID
        Route::put('/{id}', 'ItemController@update');
    
        // Eliminar un item por ID
        Route::delete('/{id}', 'ItemController@destroy');
    });
});