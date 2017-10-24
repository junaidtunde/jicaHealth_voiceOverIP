<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Survey;
use App\Log;
use Session;
use App\QuestionResponse;

use Twilio\Twiml;

class SurveyController extends Controller
{

    public function showResults($surveyId)
    {
        $survey = Survey::find($surveyId);
        $responsesByCall = QuestionResponse::responsesForSurveyByCall($surveyId)
                         ->get()
                         ->groupBy('session_sid')
                         ->values();

        return response()->view(
            'surveys.results',
            ['survey' => $survey, 'responses' => $responsesByCall]
        );
    }

    public function showFirstSurveyResults()
    {
        $firstSurvey = $this->_getFirstSurvey();
        return redirect(route('survey.results', ['survey' => $firstSurvey->id]))
                ->setStatusCode(303);
    }

    public function connectVoice(Request $request, $id, $lang)
    {
        session()->put('count', 1);
        $response = new Twiml();
        $redirectResponse = $this->_redirectWithFirstSurvey('survey.show.voice', $response, $id, $lang);
        return $this->_responseWithXmlType($redirectResponse);
    }
    
    public function showVoice($id, $lang)
    {
        $surveyToTake = Survey::find($id);
        $voiceResponse = new Twiml();

        if (is_null($surveyToTake)) {
            return $this->_responseWithXmlType($this->_noSuchVoiceSurvey($voiceResponse));
        }
        
        $voiceResponse->redirect($this->_urlForFirstQuestion($surveyToTake, $lang, 'voice'), ['method' => 'GET']);

        return $this->_responseWithXmlType(response($voiceResponse));
    }

    public function logEvent($id, Request $request)
    {
        $createLog = Log::create(['survey_id'=>$id,'recipient_phone'=>$request->To, 'call_status'=>$request->CallStatus, 'duration'=>$request->CallDuration]);
    }
    
    private function _redirectWithFirstSurvey($routeName, $response, $id, $lang)
    {
        $firstSurvey = Survey::find($id);

        if (is_null($firstSurvey)) {
            if ($routeName === 'survey.show.voice') {
                return $this->_noSuchVoiceSurvey($response);
            }
            return $this->_noSuchSmsSurvey($response);
        }

        $response->redirect(
            route($routeName, ['id' => $firstSurvey->id, 'lang' => $lang]),
            ['method' => 'GET']
        );
        return response($response);
    }

    private function _noActiveSurvey($currentQuestion, $surveySession)
    {
        $noCurrentQuestion = is_null($currentQuestion) || $currentQuestion == 'deleted';
        $noSurveySession = is_null($surveySession) || $surveySession == 'deleted';

        return $noCurrentQuestion || $noSurveySession;
    }
    
    private function _urlForFirstQuestion($survey, $lang, $routeType)
    {
        return route(
            'question.show.' . $routeType,
            ['survey' => $survey->id,
             'question' => $survey->questions()->orderBy('id')->first()->id,
             'lang' => $lang
            ]
        );
    }

    private function _noSuchVoiceSurvey($voiceResponse)
    {
        $voiceResponse->say('Sorry, we could not find the survey to take');
        $voiceResponse->say('Good-bye');
        $voiceResponse->hangup();

        return response($voiceResponse);
    }

    private function _getFirstSurvey($id)
    {
        return Survey::orderBy('id', 'DESC')->get()->first();
    }

    private function _responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }
}
