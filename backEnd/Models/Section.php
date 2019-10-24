<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['name'];


    public function contacts() {
        return $this->hasMany('App\Contact');
    }

    public function users() {
        return $this->hasMany('App\User');
    }

}
