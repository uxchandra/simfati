<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CheckItemController;
use App\Http\Controllers\GeneralCheckupController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\RepairRequestController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user/get-data', [UserController::class, 'getData'])->name('user.getData');
    Route::resource('user', UserController::class);

    Route::get('/roles/get-data', [RolesController::class, 'getDataRole']);
    Route::resource('/roles', RolesController::class);

    Route::get('/part/get-data', [PartController::class, 'getData'])->name('part.getData');
    Route::resource('part', PartController::class);

    Route::get('/machine/get-data', [MachineController::class, 'getData'])->name('machine.getData');
    Route::resource('machine', MachineController::class);

    // Route untuk CheckItem
    Route::resource('checkitem', CheckItemController::class)->except(['show']);
    Route::get('/checkitem/edit/{type}/{id}', [CheckItemController::class, 'edit'])->name('checkitem.edit');
    Route::put('/checkitem/{type}/{id}', [CheckItemController::class, 'update'])->name('checkitem.update');
    Route::delete('/checkitem/{type}/{id}', [CheckItemController::class, 'destroy'])->name('checkitem.destroy');
    Route::get('/checkitem/get-data', [CheckItemController::class, 'getData'])->name('checkitem.getData');
    Route::get('/checkitem/get-options', [CheckItemController::class, 'getOptions'])->name('checkitem.getOptions');
    Route::get('/checkitem/detail/{type}/{id}', [CheckItemController::class, 'getDetail'])->name('checkitem.detail');

    // Route untuk General Checkup
    Route::get('/general-checkup/get-data', [GeneralCheckupController::class, 'getData'])->name('general-checkup.getData');
    Route::get('/general-checkup/detail/{id}', [GeneralCheckupController::class, 'getDetail'])->name('general-checkup.getDetail');
    Route::get('/general-checkup/get-check-items', [GeneralCheckupController::class, 'getCheckItems'])->name('general-checkup.getCheckItems');
    Route::resource('general-checkup', GeneralCheckupController::class);

    // Route untuk Maintenance Schedule
    Route::get('/maintenance-schedule/get-data', [MaintenanceScheduleController::class, 'getData'])->name('maintenance-schedule.getData');
    Route::get('/maintenance-schedule/get-options', [MaintenanceScheduleController::class, 'getOptions'])->name('maintenance-schedule.getOptions');
    Route::resource('maintenance-schedule', MaintenanceScheduleController::class);

    // Route untuk Repair Request
    Route::get('/repair-request/get-data', [RepairRequestController::class, 'getData'])->name('repair-request.getData');
    Route::get('/repair-request/detail/{id}', [RepairRequestController::class, 'getDetail'])->name('repair-request.getDetail');
    Route::get('/repair-request/get-options', [RepairRequestController::class, 'getOptions'])->name('repair-request.getOptions');
    Route::resource('repair-request', RepairRequestController::class);
});

require __DIR__.'/auth.php';
