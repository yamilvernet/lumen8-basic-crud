<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

// Listar todos los items
Route::get('/items', 'ItemController@index');

// Obtener un item por ID
Route::get('/items/{id}', 'ItemController@show');

// Crear un nuevo item
Route::post('/items', 'ItemController@store');

// Actualizar un item por ID
Route::put('/items/{id}', 'ItemController@update');

// Eliminar un item por ID
Route::delete('/items/{id}', 'ItemController@destroy');

