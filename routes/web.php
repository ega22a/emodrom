<?php

use App\Http\Controllers\HostController;
use App\Http\Controllers\InterrogationController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

// Host: created and driven from the host's own phone.
Route::get('/', [HostController::class, 'create'])->name('host.create');
Route::post('/lobbies', [HostController::class, 'store'])->name('host.store');
Route::get('/host/{lobby:code}/setup', [HostController::class, 'setup'])->name('host.setup');
Route::post('/host/{lobby:code}/setup', [HostController::class, 'configure'])->name('host.configure');
Route::get('/host/{lobby:code}', [HostController::class, 'show'])->name('host.show');
Route::post('/host/{lobby:code}/close', [HostController::class, 'close'])->name('host.close');

Route::post('/host/{lobby:code}/round', [RoundController::class, 'store'])->name('host.round.store');
Route::post('/host/{lobby:code}/round/reveal', [RoundController::class, 'reveal'])->name('host.round.reveal');
Route::post('/host/{lobby:code}/round/cancel', [RoundController::class, 'cancel'])->name('host.round.cancel');
Route::post('/host/{lobby:code}/round/reader', [RoundController::class, 'reassignReader'])->name('host.round.reassign-reader');

Route::post('/host/{lobby:code}/interrogation/{interrogation}/award', [InterrogationController::class, 'award'])->name('host.interrogation.award');
Route::post('/host/{lobby:code}/interrogation/skip', [InterrogationController::class, 'skip'])->name('host.interrogation.skip');

// Public display, meant for the projector — no controls, nothing private.
Route::get('/screen/{lobby:code}', [ScreenController::class, 'show'])->name('screen.show');

Route::view('/about', 'about')->name('about');

Route::get('/lobbies/{lobby:code}/qr', [QrCodeController::class, 'show'])->name('lobbies.qr');

// Players: join from a QR code scanned off the screen, play from their own phone.
Route::get('/join/{lobby:code}', [PlayerController::class, 'create'])->name('lobbies.join');
Route::post('/join/{lobby:code}', [PlayerController::class, 'store'])->name('lobbies.join.store');
Route::get('/play/{lobby:code}', [PlayerController::class, 'show'])->name('lobbies.play');
Route::get('/play/{lobby:code}/state', [PlayerController::class, 'state'])->name('lobbies.play.state');
Route::post('/play/{lobby:code}/vote', [VoteController::class, 'store'])->name('lobbies.vote');
Route::post('/play/{lobby:code}/reader-emotion', [ReaderController::class, 'store'])->name('lobbies.reader-emotion');
Route::post('/play/{lobby:code}/purchase', [PurchaseController::class, 'store'])->name('lobbies.purchase');
