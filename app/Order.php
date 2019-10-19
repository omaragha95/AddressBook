<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['contact_id'];

    public function contact() {
        return $this->hasOne('App\Contact');
    }
}
