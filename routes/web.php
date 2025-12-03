<?php

use App\Events\MessageSent;
use App\Events\PrivateNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('chat', function () {
    event(new PrivateNotification('Hello World', 1));
    return 'Message sent!';
});
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::post('/notify', function(){
    $userId = request('user_id');
    $message = request('message');

    event(new PrivateNotification($message, $userId));

    return 'Notification sent!';
});     
require __DIR__.'/auth.php';
