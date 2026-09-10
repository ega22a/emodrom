<?php

use App\Http\Controllers\LobbyController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LobbyController::class, 'create'])->name('lobbies.create');
Route::post('/lobbies', [LobbyController::class, 'store'])->name('lobbies.store');
Route::get('/lobbies/{lobby:code}', [LobbyController::class, 'show'])->name('lobbies.show');
Route::post('/lobbies/{lobby:code}/close', [LobbyController::class, 'close'])->name('lobbies.close');
Route::post('/lobbies/{lobby:code}/rounds', [RoundController::class, 'store'])->name('lobbies.rounds.store');
Route::get('/lobbies/{lobby:code}/qr', [QrCodeController::class, 'show'])->name('lobbies.qr');

Route::get('/join/{lobby:code}', [PlayerController::class, 'create'])->name('lobbies.join');
Route::post('/join/{lobby:code}', [PlayerController::class, 'store'])->name('lobbies.join.store');
Route::get('/play/{lobby:code}', [PlayerController::class, 'show'])->name('lobbies.play');
Route::get('/play/{lobby:code}/state', [PlayerController::class, 'state'])->name('lobbies.play.state');
Route::post('/play/{lobby:code}/vote', [VoteController::class, 'store'])->name('lobbies.vote');
