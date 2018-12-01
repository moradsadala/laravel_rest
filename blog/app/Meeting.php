<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'title' , 'descrtiption' , 'time'
    ];

    public function users(){
        $this->belongsToMany('App/User');
    }
}
