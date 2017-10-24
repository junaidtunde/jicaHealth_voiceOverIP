<?php

use Illuminate\Http\RedirectResponse;

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

Route::get(
    '/survey/{survey}/results',
    ['as' => 'survey.results', 'uses' => 'SurveyController@showResults']
);
Route::get(
    '/',
    ['as' => 'root', 'uses' => 'SurveyController@showFirstSurveyResults']
);
Route::post(
    '/voice/connect/{id}/{lang}',
    ['as' => 'voice.connect', 'uses' => 'SurveyController@connectVoice']
);
Route::post(
    '/voice/event/{id}',
    ['as' => 'voice.event', 'uses' => 'SurveyController@logEvent']
);
Route::get(
    '/survey/{id}/voice/{lang}',
    ['as' => 'survey.show.voice', 'uses' => 'SurveyController@showVoice']
);
Route::get(
    '/survey/{survey}/question/{question}/voice/{lang}',
    ['as' => 'question.show.voice', 'uses' => 'QuestionController@showVoice']
);
Route::post(
    '/survey/{survey}/question/{question}/response/voice/{lang}/{isnull}',
    ['as' => 'response.store.voice', 'uses' => 'QuestionResponseController@storeVoice']
);
Route::post(
    '/survey/{survey}/question/{question}/response/transcription',
    ['as' => 'response.transcription.store', 'uses' => 'QuestionResponseController@storeTranscription']
);


/** Custom Routes **/

Route::get(
    '/init/routine',
    ['as' => 'init.app', 'uses' => 'MainController@initApp']
);

Route::get(
    '/init/firstcall/{time}/{surveyNum}',
    ['as' => 'init.firstcall', 'uses' => 'MainController@initFirstCall']
);

Route::get(
    '/init/sort-recipient',
    ['as' => 'init.sortrecipient', 'uses' => 'MainController@sortRecipient']
);

Route::get(
    '/get',
    ['as' => 'stuff', 'uses' => 'QuestionResponseController@test_this']
);

Route::resource('/test', 'MainController@sortRecipient');
