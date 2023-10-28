<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//import controllers
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\WriterrController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Registration
Route::post('create-reader', [ReaderController::class, 'register']);
Route::post('create-writer', [WriterrController::class, 'register']);

//Profile pic upload
Route::post('upload-profile-pic-reader', [ReaderController::class, 'upload_profile_pic_reader']);
Route::post('upload-profile-pic-writer', [WriterrController::class, 'upload_profile_pic_writer']);

//login
Route::post('login-reader', [ReaderController::class, 'login']);
Route::post('login-writer', [WriterrController::class, 'login']);

//Add a new book
Route::post('add-book', [WriterrController::class, 'add_book']);
Route::post('add-book-image', [WriterrController::class, 'add_book_image']);
Route::post('add-book-file', [WriterrController::class, 'add_book_file']);

//Get writer published book list
Route::get('get-writer-books/{id}', [WriterrController::class, 'get_writer_books']);

//Delete a book writer
Route::get('delete-book-writer/{id}', [WriterrController::class, 'delete_book_writer']);

//Edit book data
Route::get('get-edit-book-data/{id}', [WriterrController::class, 'get_edit_book_data']);
//Update book data
Route::post('update-book-data', [WriterrController::class, 'update_book_data']);
//Update book image
Route::post('update-book-image', [WriterrController::class, 'update_book_image']);

//Delete user
Route::get('delete-writer/{id}', [WriterrController::class, 'delete_writer']);

//Get all books
Route::get('get-all-books', [ReaderController::class, 'get_all_books']);

//Get single book reader preview
Route::get('get-single-book/{id}', [ReaderController::class, 'get_single_book']);

//Checkout Book
Route::post('checkout', [ReaderController::class, 'checkout']);

//Get purchased books
Route::get('get-purchased-books/{id}', [ReaderController::class, 'get_purchased_books']);

//Delete purchased book
Route::get('delete-purchased-book/{id}', [ReaderController::class, 'delete_purchased_book']);

//Delete reader
Route::get('delete-reader/{id}', [ReaderController::class, 'delete_reader']);

//Get categories
Route::get('get-categories', [WriterrController::class, 'get_categories']);

//Get all books
Route::get('get-categories-books/{id}', [ReaderController::class, 'get_categories_books']);

//Search books
Route::post('search-books', [ReaderController::class, 'search_books']);


Route::get('test', [WriterrController::class, 'test']);
