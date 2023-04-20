<?php

use Illuminate\Support\Facades\Route;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/storage/backendfiles/{id}/{folder_id}/{filename}', function ($id, $folder_id, $filename) {
    $path = storage_path('app/public/backendfiles/' . $id . '/' . $folder_id. "/" . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);
    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});


Route::get('/{path?}', function () {
    return view('welcome');
})->where('path', '.*');

