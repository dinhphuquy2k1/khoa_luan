<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhpSpreadSheetController;
use App\Enums\TypeProperty;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('index',['type_property'=>json_encode(TypeProperty::toSelectArray())]);
// });

// Route::get('/', function () {
//     return view('index');
// });

Route::post('/saveData', [PhpSpreadSheetController::class,'saveData']);
Route::post('/saveExam', [PhpSpreadSheetController::class,'saveExam']);


Route::post('/listExam', [PhpSpreadSheetController::class,'listExam']);
Route::post('/getExamSessionInfos/{id}', [PhpSpreadSheetController::class,'getExamSessionInfos']); //lấy danh sách ca thi theo mã kì thi
Route::get('/getAssessmentInfos', [PhpSpreadSheetController::class,'getAssessmentInfos']); //lấy danh sách mã đề theo mã ca thi
Route::get('/mark',[PhpSpreadSheetController::class,'mark']);

Route::get('/result', function () {
    return view('result');
});

Route::post('/create_excel_bank',[PhpSpreadSheetController::class,'create_excel_bank']);
Route::post('/create_exam_session',[PhpSpreadSheetController::class,'create_exam_session']); //tạo ca thi

Route::post('/create_detail_exam_session',[PhpSpreadSheetController::class,'create_detail_exam_session']); //tạo ca thi

Route::post('/getExamByExamSessionId',[PhpSpreadSheetController::class,'getExamByExamSessionId']); //lấy danh sách đề theo mã ca thi


Route::get('/extractExam',[PhpSpreadSheetController::class,'extractExam']);
Route::post('/upload',[PhpSpreadSheetController::class,'upload'])->name('upload');

Route::get('/uploadFileExam',function(){
    return view('upload');
}); //tải file danh sách lên

Route::post('/uploadFileExam',[PhpSpreadSheetController::class,'upload']); //tải file danh sách lên
//router view ảnh hướng đến định tuyến laravel nên
//đặt ở cuối và sử dụng Auth::routes(); để loại bỏ sự ảnh hưởng
// Auth::routes();

Route::get('/{any?}', function () {
    return view('index');
  })->where('any', '.*$');


