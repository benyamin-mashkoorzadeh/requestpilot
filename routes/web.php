<?php

use App\Models\Inquiry;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.overview')->name('overview');
    Route::view('/inquiries', 'admin.inquiries')->name('inquiries');
    Route::get('/inquiries/{inquiry}', function (Inquiry $inquiry) {
        return view('admin.inquiry', ['inquiry' => $inquiry]);
    })->name('inquiries.show');
    Route::view('/human-review', 'admin.human-review')->name('review');
});
