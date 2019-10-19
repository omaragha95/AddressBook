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


    public function order() {
        return $this->hasOne('App\Order');
    }
}
