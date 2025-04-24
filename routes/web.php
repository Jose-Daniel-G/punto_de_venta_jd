    <?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CategoriaController;

use App\Http\Controllers\CompraController;
use App\Http\Controllers\NotificacionesPorAvisoController;
    use App\Http\Controllers\HomeController;
use App\Http\Controllers\marcaController;
use App\Http\Controllers\NotificacionAvisoController;
    use App\Http\Controllers\PostController;
use App\Http\Controllers\presentacioneController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\proveedorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;


    // Route::get('/prueba', function () {return view('index-prueba');});
    // Route::get('/', function () {return view('welcome');});;
    // Route::get('/', [homeController::class, 'index'])->name('panel');
    Route::get('/', function () {return view('auth.login');});

    // Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])
        ->group(function () {
            Route::get('/dashboard', function () {
            // return redirect()->route('panel');
                return view('/panel/index'); //dashboard
            })->name('panel');
        });


    Route::resources([
        'categorias' => CategoriaController::class,
        'presentaciones' => presentacioneController::class,
        'marcas' => marcaController::class,
        'productos' => ProductoController::class,
        'clientes' => ClienteController::class,
        'proveedores' => proveedorController::class,
        'compras' => compraController::class,
        'ventas' => ventaController::class,
        'users' => UserController::class,
        'roles' => roleController::class,
        'profile' => profileController::class
    ]);

    Route::get('/401', function () {
        return view('pages.401');
    });
    Route::get('/404', function () {
        return view('pages.404');
    });
    Route::get('/500', function () {
        return view('pages.500');
    });
