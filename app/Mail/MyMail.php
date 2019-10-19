<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $user;
    public $first_name;
    public $last_name;
    public $mobile;
    public $address;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($title, $user, $name, $first_name, $last_name, $mobile, $address)
    {
        $this->title = $title;
        $this->user = $user;
        $this->name = $name;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->mobile = $mobile;
        $this->address = $address;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $title = $this->title;
        $user = $this->user;
        $name = $this->name;
        $first_name = $this->first_name;
        $last_name = $this->last_name;
        $mobile = $this->mobile;
        $address = $this->address;
        return $this->from('samer@gmail.com')->view('email', compact('title', 'user', 'name', 'first_name', 'last_name', 'mobile', 'address'));
    }
}
