<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Question;
use Twilio\Twiml;
use Session;

class QuestionController extends Controller
{
    public function showVoice($surveyId, $questionId, $lang)
    {
        $questionToAsk = Question::find($questionId);
        $question_lang = $lang;
        return $this->_responseWithXmlType($this->_commandForVoice($questionToAsk, $question_lang));
    }
    

    // Checks what command to carry out for the question, whether its to play() or say()
    private function _commandForVoice($question, $lang)
    {
        $voiceResponse = new Twiml();
        
        if ($question->question_id) {
            $voiceResponse->play($question->body . $lang. '.mp3');
        }
        $voiceResponse = $this->_registerResponseCommand($voiceResponse, $question, $lang);

        return response($voiceResponse);
    }

    // For Responses To Questions, if there is a response and there is no response
    private function _registerResponseCommand($voiceResponse, $question, $lang)
    {
        
        $storeResponseURL = route(
            'response.store.voice',
            ['question' => $question->id,
             'survey' => $question->survey->id,
             'lang' => $lang,
             'isnull' => 0],
            false
        );
        
        $noResponseURL = route(
            'response.store.voice',
            ['question' => $question->id,
             'survey' => $question->survey->id,
             'lang' => $lang,
             'isnull' => 1],
            false
        );
        
        //attack here
        if ($question->kind === 'yes-no') {
            $voiceResponse->gather(['method' => 'POST', 'action' => $storeResponseURL, 'finishOnKey' => '#']);
            $voiceResponse->redirect($noResponseURL, ['method' => 'POST']);
        } elseif ($question->kind === 'numeric') {
            $voiceResponse->gather(['method' => 'POST', 'action' => $storeResponseURL, 'finishOnKey' => '#']);
            $voiceResponse->redirect($noResponseURL, ['method' => 'POST']);
        }
        return $voiceResponse;
    }

    private function _responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }
}
