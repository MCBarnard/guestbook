<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::group(['prefix' => 'admin', 'middleware' => 'is_admin'], function($router) {
    Route::get('', 'AppController@index')->name('admin');
    $router->get('messages/all', 'MessageController@all')->name('messages');
    $router->get('messages/{id}/delete', 'MessageController@deleteMessage')->where('id', '[0-9]+');
    $router->put('messages/{id}/edit', 'MessageController@updateMessage')->where('id', '[0-9]+');
    $router->get('messages/{id}', 'MessageController@readMessages')->where('id', '[0-9]+')->name('messages.read');
    $router->post('messages/{id}', 'MessageController@newMessage')->where('id', '[0-9]+')->name('messages.new');
});
Route::group(['prefix' => 'home', 'middleware' => 'auth'], function($router) {
    $router->get('', 'MessageController@readMessages');
    $router->get('user', 'HomeController@getUser');
    $router->post('new', 'MessageController@newMessage');
    $router->put('{id}/edit', 'MessageController@updateMessage')->where('id', '[0-9]+');
    $router->get('{id}/delete', 'MessageController@deleteMessage')->where('id', '[0-9]+');
});
Route::group(['middleware' => 'auth'], function($router) {
    $router->get('/{any}', 'AppController@index')->where('any', '.*');
});
