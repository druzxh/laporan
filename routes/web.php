<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Admin\Pages\ReportGenerator;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('/admin/report-generator/generate-pdf', [ReportGenerator::class, 'generatePDF'])->name('filament.admin.report-generator.generate-pdf');

require __DIR__.'/auth.php';
