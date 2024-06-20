<?php

use App\Events\NewsHidden;
use App\Http\Controllers\PdfGeneratorController;
use App\Models\News;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormProcessor;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', fn() => view('main.home', [
    'name' => 'Name',
    'age' => '18',
    'position' => 'Position',
    'address' => 'Address',
]))->name('home');
Route::get('/contacts', fn() => view('main.contacts', [
    'address' => 'Address',
    'post_code' => 'Post code',
    'email' => '',
    'phone' => '8 800 XXX XX XX',
]))->name('contacts');

Route::get('/userform', [FormProcessor::class, 'index'])->name('userform');
Route::post('/store_form', [FormProcessor::class, 'store'])->name('store_form');

Route::get('/employee', [EmployeeController::class, 'index'])->name('employee');
Route::post('/employee_store', [EmployeeController::class, 'store'])->name('employee_store');
Route::put('/employee/{id}', [EmployeeController::class, 'update']);

Route::get('/book', [BookController::class, 'index'])->name('book');
Route::post('/book_store', [BookController::class, 'store'])->name('book_store');

Route::get('/test_database', function () {
    $employee = new \App\Models\Employee();
    return (object)['result' => $employee->save()];
})->name('test_database');

Route::get('/user', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'get']);
Route::post('/store-user', [UserController::class, 'store']);
Route::get('/resume/{id}', [PdfGeneratorController::class, 'index']);

Route::get('/logs', [LogController::class, 'index']);
Route::get('/logs/{page}', [LogController::class, 'index']);

Route::get('news/create-test', function () {
    $news = new News();
    $news->title = 'Test news title';
    $news->body = 'Test news body';
    $news->save();
    return $news;
});
Route::get('news/{id}/hide', function ($id) {
    /** @var News $news */
    $news = News::query()->findOrFail($id);
    $news->hidden = true;
    $news->save();
    NewsHidden::dispatch($news);
    return 'News hidden';
});
