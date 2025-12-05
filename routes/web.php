<?php

use App\Events\MessageSent;
use App\Events\PrivateNotification;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/chat', [MessageController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [MessageController::class, 'send'])->name('chat.send');
    Route::get('/video-call', function () {
        return view('video-call.index');
    })->name('video-call.index');
    Route::get('/api/users', function () {
        return User::where('id', '!=', auth()->id())->get(['id', 'name']);
    });
    Route::post('/broadcast/video-call-invite', function (Request $request) {
        $request->validate([
            'roomId' => 'required|string',
            'toUserId' => 'required|integer',
            'fromUserName' => 'required|string',
        ]);
        broadcast(new \App\Events\VideoCallInvite($request->roomId, auth()->id(), $request->fromUserName, $request->toUserId));
        return response()->json(['status' => 'Invite sent']);
    });

    Route::post('/broadcast/video-call-decline', function (Request $request) {
        $request->validate([
            'roomId' => 'required|string',
            'toUserId' => 'required|integer',
            'fromUserName' => 'required|string',
        ]);
        // Parse fromUserId from roomId: room-fromUserId-toUserId-timestamp
        $parts = explode('-', $request->roomId);
        $fromUserId = (int)$parts[1];
        broadcast(new \App\Events\VideoCallDeclined($request->roomId, $fromUserId, $request->fromUserName, $request->toUserId));
        return response()->json(['status' => 'Decline sent']);
    });

    Route::post('/broadcast/video-call-busy', function (Request $request) {
        $request->validate([
            'fromUserName' => 'required|string',
        ]);
        broadcast(new \App\Events\VideoCallBusy(auth()->id(), $request->fromUserName));
        return response()->json(['status' => 'Busy sent']);
    });

    Route::post('/broadcast/video-call-end', function (Request $request) {
        $request->validate([
            'roomId' => 'required|string',
            'fromUserName' => 'required|string',
        ]);
        // Parse toUserId from roomId: room-fromUserId-toUserId-timestamp
        $parts = explode('-', $request->roomId);
        $fromUserId = (int)$parts[1];
        $toUserId = (int)$parts[2];
        // If current user is fromUserId, broadcast to toUserId, else reverse
        $currentUserId = auth()->id();
        if ($currentUserId == $fromUserId) {
            broadcast(new \App\Events\VideoCallEnded($request->roomId, $fromUserId, $request->fromUserName, $toUserId));
        } else {
            broadcast(new \App\Events\VideoCallEnded($request->roomId, $toUserId, $request->fromUserName, $fromUserId));
        }
        return response()->json(['status' => 'End sent']);
    });
});
require __DIR__.'/auth.php';
