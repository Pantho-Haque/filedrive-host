<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PasswordResetController;



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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post("/register", [UserController::class, 'register']);
Route::get("/emailverified/{token}", [UserController::class, 'emailverified']);
Route::post("/login", [UserController::class, 'login']);

Route::post("/resetPassemailsend", [PasswordResetController::class, 'send_reset_password_email']);


Route::middleware(['auth:sanctum','verified'])->group(function () {
    Route::get("/me", [UserController::class, 'me']);
    Route::get("/users/search/{query}", [UserController::class, 'search']);
    Route::post("/logout", [UserController::class, 'logout']);
    Route::post("/bugfeedback", [UserController::class, 'bugfeedback']);
     Route::post("/updatenameemail", [UserController::class, 'updateNameEmail']);
    Route::post("/changepassword", [UserController::class, 'change_password']);
    Route::post("/changepassword", [UserController::class, 'change_password']);
    
    // admin 
    Route::get("/users", [UserController::class, 'index']);
    Route::get("/admins", [UserController::class, 'adminindex']);
    Route::get("/graph", [UserController::class, 'graph']);
    Route::get("/setresetadmin/{id}", [UserController::class, 'setresetadmin']);
    Route::get("/useractivation/{id}", [UserController::class, 'useractivation']);
    Route::get("/resettheuser/{id}", [UserController::class, 'resettheuser']);
    Route::get("/getallbugsfeedbacks", [UserController::class, 'getallbugsfeedbacks']);


    // folders
    Route::post('/createfolder', [FolderController::class, 'store']);
    Route::delete('/deletefolder/{id}', [FolderController::class, 'destroy']);
    Route::get('/folders/{id?}',[FolderController::class, 'index']);

    // files
    Route::post('/uploadfile', [FileController::class, 'store']);
    Route::delete('/deletefile/{id}', [FileController::class, 'destroy']);
    Route::get('/files/{id?}',[FileController::class, 'index']);
    Route::get('/filesoffolder/{id}',[FileController::class, 'showfiles']);
});


// Route::get("/users/{id}", [UserController::class, 'show']);
// Route::post("/users", [UserController::class, 'store']);
// Route::put("/users/{id}", [UserController::class, 'update']);
// Route::delete("/users/{id}", [UserController::class, 'destroy']);


/*

DB_HOST=localhost
DB_DATABASE=filedrive
DB_USERNAME=pantho
DB_PASSWORD=jJIW3_Oqo7@9zXio

*/