<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use DB;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\User;
use Session;
use Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Twilio\Rest\Client;
use Twilio\Twiml;
use App\Survey;
use App\Question;
use App\QuestionResponse;
use App\Log;
use DateTime;

class MainController extends Controller
{
    public function initApp()
    {
    }
    
    public function initFirstCall($time, $surveyNum)
    {
        //query db for recipients at $time
        $phones = DB::connection()->select("SELECT phone_number FROM recipients WHERE preferred_time=10");

        // To get the preferred language of the recipient
        if ($phones) {
            $language = DB::connection()->select("SELECT preferred_lang FROM recipients WHERE preferred_time=10");
        }
        
        //for each of them make a twilio call
        
        $AccountSid = "ACbbb2bdc97932964b709697702be99690";
        $AuthToken = "ee536c11385b0bd9864687b90f49cd41";
        $client = new Client($AccountSid, $AuthToken);
        $my_twilio_num = "+441478272049";
        $host = "http://72236abc.ngrok.io/";
        $survey_id = $surveyNum;

        foreach ($phones as $tel) {
            foreach ($language as $lang) {
                try {
                    $call = $client->account->calls->create(
                        $tel->phone_number,
                        $my_twilio_num,
                        array(
                            "url" => $host."/voice/connect/".$survey_id."/".$lang->preferred_lang,
                            "statusCallbackMethod" => "POST",
                            "statusCallback" => "$host/voice/event/$survey_id",
                            "statusCallbackEvent" => array(
                        //                        "completed", "busy", "failed", "no-answer", "cancelled"
                                "initiated", "ringing", "answered", "completed"
                        ))
                    );
                    $success= "Started call to: " . $call->to;
                } catch (Exception $e) {
                    $error =  "Error: " . $e->getMessage();
                }
            }
        }
    }
    
    public function sortRecipient()
    {
            // $initialSurveyRespondents = Log::where('call_status', 'completed')->where('survey_id', 1)->get();
            $pregnant = array();
            $delivered = array();
            $month_days = [31,28,31,30,31,30,31,31,30,31,30,31];
            $month_names = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            $thirdSurveyRespondents = QuestionResponse::where('question_num', 0.3)->get();
            $fifthSurveyRespondents = QuestionResponse::where('question_num', 0.5)->get();


        foreach ($thirdSurveyRespondents as $respondents) {
            if ($respondents->response == 2) {
                DB::update('update recipients set status = "pregnant" where phone_number = ?', [$respondents->recipient]);
                array_push($pregnant, $respondents->recipient);
            } elseif ($respondents->response == 1) {
                foreach ($fifthSurveyRespondents as $respondent) {
                    if ($respondents->recipient == $respondent->recipient) {
                        $get_month = QuestionResponse::where('recipient', $respondent->recipient)->where('question_num', 0.4)->get();

                        $get_day = QuestionResponse::where('recipient', $respondent->recipient)->where('question_num', 0.5)->get();
                
                        $day = $get_day->get(0)->response;
                        $month = $get_month->get(0)->response;
                        $month_name = $month_names[$month-1];
                        $year = date("Y");
                        $time = date("m/d/Y");

                        $birthDate = new DateTime($month."/".$day."/".$year);
                        $current_time = new DateTime($time);

                        $days_of_baby = date_diff($birthDate, $current_time)->format('%a');
                
                        $level = ceil($days_of_baby/7);
                
                        // $ready = ($level < 29) ? 1 : 0;


                        DB::update('update recipients set status = "delivered", delivery_month = ?, delivery_day = ?, babys_age = ?, delivery_stage = ? where phone_number = ?', [$month_name, $day, $days_of_baby, $level ,$respondents->recipient]);
                        array_push($delivered, $respondents->recipient);
                    }
                }
            }
        }
    }
}
