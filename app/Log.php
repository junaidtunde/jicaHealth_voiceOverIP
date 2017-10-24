<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = ['survey_id','recipient_phone','call_status','duration'];
    protected $primaryKey = 'log_id';
}