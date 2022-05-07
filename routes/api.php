<?php

use Illuminate\Support\Facades\Route;
use Porygon\Meeting\Http\Controllers\Api\AppointmentController;

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'middleware' => config('admin.route.middleware'),
    "as" => "dcat.admin."
], function () {
});
Route::group(["middleware" => "api"], function () {
    Route::group(["as" => "api."], function () {
        Route::resource("appointments", AppointmentController::class)->only("store", "update", "destroy");
    });
});
