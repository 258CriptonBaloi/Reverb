<?php

use App\Events\PrivateEvent;
use App\Events\PublicEvent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function(){
    /* event(new PublicEvent('hello world')); */
    event(new PrivateEvent('private message', 1));
});