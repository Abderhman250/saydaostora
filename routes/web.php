<?php

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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::middleware(['admin'])->group(function () {
    Route::get('/request','WinerController@index')->name('request');
    Route::post('changestatus/{id}','WinerController@changestatus')->name('changestatus');
    Route::get('/home', 'HomeController@index')->name('home');
    // users
    Route::get('/users', 'API\UserController@index')->name('users');
    Route::get('/users/edit/{id}', 'API\UserController@edit')->name('users.edit');
    Route::get('/users/delete/{id}', 'API\UserController@delete')->name('users.delete');
    Route::post('/users/update/{id}', 'API\UserController@update')->name('users.update');

    //questions & answers
    Route::get('/questions/create', 'QuestionsController@create')->name('question.create');
    Route::post('/questions/store', 'QuestionsController@store')->name('question.store');
    Route::post('/questions/correct_answer', 'QuestionsController@setCorrectAnswer')->name('question.correct_answer');
    Route::get('/questions', 'QuestionsController@index')->name('question.index');
    Route::get('/questions/edit/{id}', 'QuestionsController@edit')->name('question.edit');
    Route::post('/questions/update/{id}', 'QuestionsController@update')->name('question.edit');
    Route::post('/answer/update-answer/{id}', 'QuestionsController@updateAnswer')->name('question.edit');
    Route::get('/questions/delete/{id}', 'QuestionsController@destroy')->name('question.delete');

    // games
    Route::get('/games','GamesController@index')->name('games.index');
    Route::get('/games/edit/{id}','GamesController@edit')->name('games.edit');
    Route::post('/games/delete/{id}','GamesController@destroy')->name('games.delete');
    Route::post('/games/update/{id}','GamesController@update')->name('games.update');
    Route::post('/games/update-attributes/{id}','GamesController@updateAttributes')->name('games.updateAttributes');
    Route::get('/games/update-attributes/{id}','GamesController@updateAttributes')->name('games.updateAttributes');
Route::prefix('task')->group(function()
{
    Route::get('show','TaskController@index')->name('show');
    Route::get('/task/create','TaskController@create')->name('create');
    Route::post('/task/store','TaskController@store')->name('store');
    Route::get('edit/{id}','TaskController@edit')->name('edit');
    Route::get('delete/{id}','TaskController@destroy')->name('destroy');
    Route::post('update/{id}','TaskController@update')->name('update');
});

Route::prefix('awards')->group(function()
{
    Route::get('create','AwardsController@create')->name('create.awards');
    Route::post('store','AwardsController@store')->name('store.awards');
    Route::get('show','AwardsController@index')->name('show.awards');
    Route::get('delete/{id}','AwardsController@destroy')->name('delete.awards');
    Route::get('edit/{id}','AwardsController@edit')->name('edit.awards');
    Route::post('update/{id}','AwardsController@update')->name('update.awards');
});
    // Route::get('request','AwardsController@request')->name('request');
    Route::get('/notifction/create','notifctionController@create');
    Route::post('/notifction/store','notifctionController@store');

});
Route::get('privacyploicy.html','PrivacyPolicyController@index');
Route::view('/', 'auth.login');





