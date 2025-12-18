<?php

use App\Http\Controllers\Api\SincronizacionController;

Route::post('/sincronizar-ventas', [SincronizacionController::class, 'recibirVentas']);