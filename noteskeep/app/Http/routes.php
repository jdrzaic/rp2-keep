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

//home
Route::get('/', 'HomeController@index');

//authentication
Route::auth();

//home
Route::get('/home', 'HomeController@index');

//third party login
Route::get('/redirect/{provider}', 'SocialAuthController@redirect');
Route::get('/callback/{provider}', 'SocialAuthController@callback');

//create new note
Route::get('/note/create', 'NoteController@create');

//edit note with id
Route::post('/note/{id}/edit', ['uses' =>'NoteController@edit']);
//share note with id
Route::post('/note/{id}/share', ['uses' =>'NoteController@share']);
//delete note with id
Route::post('/note/{id}/delete', ['uses' => 'NoteController@delete']);
//get note with id
Route::get('/note/{id}', 'NoteController@index');

//get all notes
Route::get('/notes', 'NotesController@index');
//get notes created by user
Route::get('/notes/my', 'NotesController@searchMyNotes');
//get notes shared by user
Route::get('/notes/other', 'NotesController@searchOtherNotes');
//get notes that match query
Route::get('/notes/search', 'NotesController@search');
//check if notes updated
Route::get('/report', 'NotesController@reportShare');
//search users
Route::get('/users', 'UsersController@search');
//get user
Route::get('/user', 'UsersController@index');
