<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'address',
        'user_id',
        'section_id'
    ];


    public function section() {
        return $this->belongsTo('App\Section');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }


    public function order() {
        return $this->hasOne('App\Order');
    }
}
