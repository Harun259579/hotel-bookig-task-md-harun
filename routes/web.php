<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::get('/', fn() => redirect()->route('booking.form'));

Route::get('/booking', [BookingController::class, 'form'])->name('booking.form');
Route::post('/booking/check', [BookingController::class, 'check'])->name('booking.check');
Route::post('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
Route::get('/booking/thank-you/{booking}', [BookingController::class, 'thankyou'])->name('booking.thankyou');

// API
Route::get('/api/disabled-dates', [BookingController::class, 'disabledDates'])->name('api.disabledDates');
