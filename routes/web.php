<?php

use Illuminate\Support\Facades\Route;

Route::get("/amo/app", [\LebedevSoft\AmoCRM\Http\Controllers\AmoAppController::class, "amoApp"]);
Route::get("/amo/auth", [\LebedevSoft\AmoCRM\Http\Controllers\AmoAppController::class, "amoAuth"]);
