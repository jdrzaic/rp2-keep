<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::get('/home', 'HomeController@index');

Route::get('/redirect/{provider}', 'SocialAuthController@redirect');
Route::get('/callback/{provider}', 'SocialAuthController@callback');

Route::get('/note/create', 'NotesController@create');

Route::post('/note/{id}/edit', ['uses' =>'NotesController@edit']);
Route::post('/note/{id}/share', ['uses' =>'NotesController@share']);
Route::post('/note/{id}/delete', ['uses' => 'NotesController@delete']);

Route::get('/notes', 'NotesController@index');
Route::get('/notes/my', 'NotesController@searchMyNotes');
Route::get('/notes/other', 'NotesController@searchOtherNotes');
Route::get('/notes/search', 'NotesController@search');

Route::get('/users', 'UsersController@search');
Route::get('/user', 'UsersController@index');