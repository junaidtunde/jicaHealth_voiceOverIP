<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Question;
use App\Survey;
use App\Log;
use App\QuestionResponse;
use App\ResponseTranscription;
use Twilio\Twiml;
use Cookie;
use Session;

class QuestionResponseController extends Controller
{
    // This also gets the count of the sessions
    public function storeVoice($surveyId, $questionId, $lang, $isnull, Request $request)
    {
        $voiceResponse = new Twiml();
        $question = Question::find($questionId);
        $digits = $request->input('Digits');
        $count = session()->get('count');
    
        if ($surveyId == 1) { // For Survey 1, which needs a repeat system
            if ($isnull == 1) { //when there is NO DTMF RESPONSE from the RECIPIENT
                if ($question->question_id == 0.1) {
                    $nextQuestion = $this->_questionAfter($question);
                    
                    return $this->_responseWithXmlType(
                        $this->_redirectToQuestion($nextQuestion, $lang, 'question.show.voice')
                    );
                } else {
                    if ($count == 0) {
                        return $this->_responseWithXmlType($this->_voiceMessageAfterFailure($voiceResponse, $question, $lang, $request));
                    } else {
                        // This is for if there is no response on the second iteration
                        session()->put('count', 0); // Put count to zero so as to terminate on the third iteration
                        return $this->_responseWithXmlType(
                            $this->_redirectToQuestion($question, $lang, 'question.show.voice')
                        );
                    }
                }
            } else { //when there is a DTMF RESPONSE from the RECIPIENT
                $newResponse = $question->responses()->create(
                    ['response' => $request->input('Digits'),
                    'type' => 'voice',
                    'recipient' => $request->input('To'),
                    'question_num' => $question->question_id,
                    'session_sid' => $request->input('CallSid')]
                );
                
                if (($question->question_id == 0.3) && ($digits == 2)) {
                    return $this->_responseWithXmlType($this->_voiceMessageAfterSuccess($voiceResponse, $question, $lang, $request));
                }elseif (($question->question_id == 0.1) && ($digits == 3)) { // This is for the repeating part               
                    return $this->_responseWithXmlType(
                        $this->_redirectToQuestion($question, $lang, 'question.show.voice')
                    );
                }
                 elseif ($question->question_id == 0.1) {
                    $nextQuestion = $this->_questionAfter($question, 2);
                    
                    return $this->_responseWithXmlType(
                        $this->_redirectToQuestion($nextQuestion, $lang, 'question.show.voice')
                    );
                } else {
                    $nextQuestion = $this->_questionAfter($question);
                    // is_null($nextQuestion)

                    if (is_null($nextQuestion)) {
                        return $this->_responseWithXmlType($this->_voiceMessageAfterSuccess($voiceResponse, $question, $lang, $request));
                    } else {
                        return $this->_responseWithXmlType(
                            $this->_redirectToQuestion($nextQuestion, $lang, 'question.show.voice')
                        );
                    }
                }
            }
        } else {
            if ($isnull == 1) { // If there is no DTMF Response for other questions in other surveys 
                if (($question->question_id == 1.2) || ($question->question_id == 2.2) || ($question->question_id == 6.3)) { // If there is no response in 1.2 and 2.2 which needs to continue
                    $nextQuestion = $this->_questionAfter($question);
                    if (is_null($nextQuestion)) {
                        return $this->_responseWithXmlType($this->_voiceMessageAfterSuccess($voiceResponse, $question, $lang, $request));
                    } else {
                        return $this->_responseWithXmlType(
                            $this->_redirectToQuestion($nextQuestion, $lang, 'question.show.voice')
                        );
                    }
                }
                else {
                    return $this->_responseWithXmlType($this->_voiceMessageAfterFailure($voiceResponse, $question, $lang, $request));
                }
            } else {
                    $newResponse = $question->responses()->create(
                        ['response' => $request->input('Digits'),
                             'type' => 'voice',
                             'recipient' => $request->input('To'),
                             'question_num' => $question->question_id,
                             'session_sid' => $request->input('CallSid')]
                    );
                        
                    if ((($question->question_id == 1.1) || ($question->question_id == 2.1) || ($question->question_id == 3.1) || ($question->question_id == 4.1 || ($question->question_id == 5.1))) && ($digits == 1)) {
                        $nextQuestion = $this->_questionSix();
                            
                        return $this->_responseWithXmlType(
                            $this->_redirectToQuestion($nextQuestion, $lang, 'question.show.voice')
                        );
                    } elseif (($question->question_id == 6.2) && ($digits == 1)) {
                        $nextQuestion = $this->_questionAfter($question, 2);
                        // is_null($nextQuestion)

                        return $this->_responseWithXmlType(
                            $this->_redirectToQuestion($nextQuestion, $lang, 'question.show.voice')
                        );
                    }
                     else {
                        $nextQuestion = $this->_questionAfter($question);
                        // is_null($nextQuestion)
        
                        if (is_null($nextQuestion)) {
                            return $this->_responseWithXmlType($this->_voiceMessageAfterSuccess($voiceResponse, $question, $lang, $request));
                        } else {
                            return $this->_responseWithXmlType(
                                $this->_redirectToQuestion($nextQuestion, $lang, 'question.show.voice')
                            );
                        }
                    }
            }
            
        }
    }
    
    private function _questionAfter($question, $step = 1)
    {
        $survey = Survey::find($question->survey_id);
        $allQuestions = $survey->questions()->orderBy('id', 'asc')->get();
        $position = $allQuestions->search($question);
        $nextQuestion = $allQuestions->get($position + $step);
        return $nextQuestion;
    }

    private function _questionSix()
    {
        $survey = Survey::find(7);
        $allQuestions = $survey->questions()->orderBy('id', 'asc')->get();
        $nextQuestion = $allQuestions[0];
        return $nextQuestion;
    }

    private function _redirectToQuestion($question, $lang, $route)
    {
        $questionUrl = route(
            $route,
            ['question' => $question->id, 'survey' => $question->survey->id, 'lang' => $lang]
        );
        $redirectResponse = new Twiml();
        $redirectResponse->redirect($questionUrl, ['method' => 'GET']);

        return response($redirectResponse);
    }
    
    public function test_this()
    {
        
        $firstSurvey = Survey::find(1);
        
        echo $firstSurvey;
    }

    private function _voiceMessageAfterFailure($voiceResponse, $question, $lang, $request)
    {
        $survey = Survey::find(1);
        $failquestion = $survey->questions()->where('question_id', 'LIKE', 0.9)->get();
        
        $newResponse = $question->responses()->create(
            ['response' => 'no-answer',
             'type' => 'voice',
             'question_id' => $question->question_id,
             'session_sid' => $request->input('To')]
        );
        
        $voiceResponse->play($failquestion->get(0)->body. $lang. '.mp3');
        $voiceResponse->hangup();
        
        return response($voiceResponse);
    }
    
    private function _voiceMessageAfterSuccess($voiceResponse, $question, $lang, $request)
    {
        $survey = Survey::find($question->survey_id);
        if ($survey->id == 1) {
            $successquestion = $survey->questions()->where('question_id', 'LIKE', 0.6)->get();
        }
        elseif ($survey->id == 2 && $question->question_id == 1.4) {
            $successquestion = $survey->questions()->where('question_id', 'LIKE', 1.4)->get();
        }
        elseif ($survey->id == 2 && $question->question_id == 6.4) {
            $successquestion = $survey->questions()->where('question_id', 'LIKE', 6.4)->get();
        }
        
        $voiceResponse->play($successquestion->get(0)->body. $lang. '.mp3');
        $voiceResponse->hangup();

        return response($voiceResponse);
    }

    private function _responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }
}
