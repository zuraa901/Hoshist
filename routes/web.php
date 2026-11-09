<?php

use App\Http\Controllers\ExploreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TitleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// Route yang hanya boleh dibuka setelah login
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile');
    Route::get('/jelajahi', [ExploreController::class, 'index'])->name('explore');

    Route::get('/judul/cari', [TitleController::class, 'search'])->name('title.search');
    Route::get('/judul/{id}', [TitleController::class, 'show'])->whereNumber('id')->name('detail.show');

    Route::get('/rating/baru', [RatingController::class, 'create'])->name('rating.create');
    Route::post('/rating', [RatingController::class, 'store'])->name('rating.store');

    Route::post('/posting', [PostController::class, 'store'])->name('post.store');
});