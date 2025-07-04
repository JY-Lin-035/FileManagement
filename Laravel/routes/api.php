<?php

use App\Http\Middleware\CheckSession;
use App\Http\Controllers\api\FileController;
use App\Http\Controllers\api\ShareController;
use App\Http\Controllers\api\FolderController;
use App\Http\Controllers\api\AccountsController;
use App\Http\Requests\EmailVerificationRequestCustom;

use Illuminate\Support\Facades\Route;



Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequestCustom $request) {
    $request->fulfill(); // 寫入時間
    return response()->json(['message' => 'Email verified successfully.']);
})->middleware(['signed'])->name('verify.verification');


Route::get('accounts/checkSession', function () {
    return response()->json(null, 200);
})->name('accounts.checkSession')->middleware(CheckSession::class);

// accounts
Route::post('accounts/register', [AccountsController::class, 'register'])->name('accounts.register')->middleware('throttle:5,30');
Route::post('accounts/login', [AccountsController::class, 'login'])->name('accounts.login')->middleware('throttle:5,10');
Route::post('accounts/signOut', [AccountsController::class, 'signOut'])->name('accounts.signOut')->middleware(CheckSession::class);

// verify code
Route::post('accounts/getCode', [AccountsController::class, 'getCode'])->name('accounts.getCode')->middleware('throttle:2,5');

// modify
Route::put('accounts/modifyMail', [AccountsController::class, 'modifyMail'])->name('accounts.modifyMail')->middleware(CheckSession::class);
Route::put('accounts/modifyPW', [AccountsController::class, 'modifyPassword'])->name('accounts.modifyPW')->middleware(CheckSession::class);

// reset
Route::put('accounts/resetPW', [AccountsController::class, 'resetPW'])->name('accounts.resetPW')->middleware('throttle:5,30');


// files
Route::get('files/getStorage', [FileController::class, 'getStorage'])->name('files.getStorage')->middleware(CheckSession::class);
Route::get('files/getFileList', [FileController::class, 'getFileList'])->name('files.getFileList')->middleware(CheckSession::class);
Route::post('files/uploadFile', [FileController::class, 'uploadFile'])->name('files.uploadFile')->middleware(CheckSession::class);
Route::get('files/download', [FileController::class, 'download'])->name('files.download')->middleware(CheckSession::class);
Route::delete('files/delete', [FileController::class, 'delete'])->name('files.delete')->middleware(CheckSession::class);

// share
Route::get('share/getList', [ShareController::class, 'getList'])->name('share.getList')->middleware(CheckSession::class);
Route::post('share/getLink', [ShareController::class, 'getLink'])->name('share.getLink')->middleware(CheckSession::class);
Route::delete('share/deleteLink', [ShareController::class, 'deleteLink'])->name('share.deleteLink')->middleware(CheckSession::class);
Route::get('share/downloadFile', [ShareController::class, 'downloadFile'])->name('share.downloadFile');

// folders
Route::post('folders/createFolder', [FolderController::class, 'createFolder'])->name('folders.createFolder')->middleware(CheckSession::class);
Route::put('folders/renameFolder', [FolderController::class, 'renameFolder'])->name('folders.renameFolder')->middleware(CheckSession::class);
Route::delete('folders/deleteFolder', [FolderController::class, 'deleteFolder'])->name('folders.deleteFolder')->middleware(CheckSession::class);
