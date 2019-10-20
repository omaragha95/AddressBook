<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['contact_id', 'user_id'];

    // public function contact() {
    //     return $this->belongTo('App\Contact');
    // }

    public function user() {
        return $this->belongsTo('App\User');
    }
}
