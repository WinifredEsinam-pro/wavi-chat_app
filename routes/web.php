<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'users' => \App\Models\User::where('id', '!=', auth()->id())->get()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/download-file/{message}', function (App\Models\Message $message) {
    if ($message->is_file && Storage::disk('public')->exists($message->file_path)) {
        return Storage::disk('public')->download($message->file_path, $message->file_name);
    }
    abort(404, 'File not found.');
})->name('file.download')->middleware('auth');



require __DIR__.'/auth.php';
