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

Route::get('/', 'HomeController@index');

Route::auth();

Route::get('/home', 'HomeController@index');

Route::get('/redirect/{provider}', 'SocialAuthController@redirect');
Route::get('/callback/{provider}', 'SocialAuthController@callback');

Route::get('/note/create', 'NoteController@create');

Route::post('/note/{id}/edit', ['uses' =>'NoteController@edit']);
Route::post('/note/{id}/share', ['uses' =>'NoteController@share']);
Route::post('/note/{id}/delete', ['uses' => 'NoteController@delete']);
Route::get('/note/{id}', 'NoteController@index');

Route::get('/notes', 'NotesController@index');
Route::get('/notes/my', 'NotesController@searchMyNotes');
Route::get('/notes/other', 'NotesController@searchOtherNotes');
Route::get('/notes/search', 'NotesController@search');
Route::get('/report', 'NotesController@reportShare');

Route::get('/users', 'UsersController@search');
Route::get('/user', 'UsersController@index');
