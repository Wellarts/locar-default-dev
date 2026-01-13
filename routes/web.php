<?php

use App\Http\Controllers\Contrato;
use App\Http\Controllers\FichaAgendamento;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoController;

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
Route::get('/', function () { return redirect('/admin'); })->name('login');

Route::get('pdf/locacao/{id}',[Contrato::class, 'printLocacao'])->name('imprimirLocacao');
Route::get('pdf/documento/{id}',[DocumentoController::class, 'ordemServico'])->name('imprimirDocumento');
Route::get('pdf/ordemServico',[DocumentoController::class, 'ordemServicoRelatorio'])->name('imprimirOrdemServicoRelatorio');
Route::get('pdf/locacoes',[DocumentoController::class, 'locacoesRelatorio'])->name('imprimirLocacoesRelatorio');
