<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    dd(\App\Helpers\ContainerHelper::getAccessTokenService());
    return view('welcome');
});