<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// MODELOS
use App\Models\Usuario;

// PROVEEDORES
use App\Http\Controllers\Proveedor\DashboardController as ProveedorDashboardController;
use App\Http\Controllers\Proveedor\CompraController as ProveedorCompraController;

// CLIENTES
use App\Http\Controllers\Cliente\DashboardClienteController;
use App\Http\Controllers\Cliente\PagoClienteController;

// TRABAJADORES
use App\Http\Controllers\Trabajador\DashboardController as TrabajadorDashboard;

// PROPIETARIO MELAMINA / CONSTRUCCIÓN
use App\Http\Controllers\Propietario\melamina\ProyectoController;
use App\Http\Controllers\Propietario\melamina\AsignacionController;
use App\Http\Controllers\Propietario\melamina\ClienteController;
use App\Http\Controllers\Propietario\melamina\UsuarioController;
use App\Http\Controllers\Propietario\melamina\PerfilController;
use App\Http\Controllers\Propietario\melamina\TrabajadorController;
use App\Http\Controllers\Propietario\melamina\ProveedorController;
use App\Http\Controllers\Propietario\melamina\CompraController;
use App\Http\Controllers\Propietario\melamina\ProductoController;
use App\Http\Controllers\Propietario\melamina\CategoriaController;
use App\Http\Controllers\Propietario\melamina\DetalleCompraController;
use App\Http\Controllers\Propietario\melamina\ServicioController;
use App\Http\Controllers\Propietario\melamina\ProyectoServicioController;
use App\Http\Controllers\Propietario\melamina\InventarioController;
use App\Http\Controllers\Propietario\melamina\UnidadMedidaController;
use App\Http\Controllers\Propietario\melamina\PagoController;
use App\Http\Controllers\Propietario\melamina\DashboardController;
use App\Http\Controllers\Propietario\melamina\ReportePagoController;

use Illuminate\Support\Facades\Http;

// ====================
// RUTAS PÚBLICAS
// ====================

// Pantalla principal (WELCOME)
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

// Login / Logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ====================
// RUTAS PROTEGIDAS
// ====================

Route::middleware(['auth'])->group(function () {

    // ====================
    // DASHBOARD GENERAL
    // ====================
    Route::get('/dashboard', function () {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $user->load(['propietario', 'cliente', 'trabajador', 'proveedor']);

        $userData = [
            'id_usuario'     => $user->id_usuario,
            'estado_usuario' => $user->estado_usuario,
            'role'           => $user->role,
            'name'           => $user->name,
            'propietario'    => $user->propietario,
            'cliente'        => $user->cliente,
            'trabajador'     => $user->trabajador,
            'proveedor'      => $user->proveedor,
        ];

        return Inertia::render('Dashboard', $userData);
    })->name('dashboard');

    // ====================
    // CLIENTE
    // ====================
// Rutas que requiere el cliente logueado
Route::prefix('cliente')
    ->middleware('auth')
    ->name('cliente.')
    ->group(function () {
        // Dashboard del cliente (menú lateral)
        // URL: /cliente/dashboard
        // Nombre: cliente.dashboard
        Route::get('/dashboard', [DashboardClienteController::class, 'index'])
            ->name('dashboard');

        // Página de pagos (Cliente/PagosCliente.vue)
        // URL: /cliente/pagos
        // Nombre: cliente.pagos.index
        Route::get('/pagos', [PagoClienteController::class, 'index'])
            ->name('pagos.index');

        // Registrar pago
        // URL: /cliente/pagos   (POST)
        // Nombre: cliente.pagos.store
        Route::post('/pagos', [PagoClienteController::class, 'store'])
            ->name('pagos.store');

        // Cuotas pendientes del proyecto del cliente logueado
        // URL: /cliente/pagos/cuotas-pendientes
        // Nombre: cliente.pagos.cuotasPendientes
        Route::get('/pagos/cuotas-pendientes', [PagoClienteController::class, 'cuotasPendientes'])
            ->name('pagos.cuotasPendientes');

        // Consultar estado de un pago QR
        // URL: /cliente/pagos/consultar
        // Nombre: cliente.pagos.consultar
        Route::post('/pagos/consultar', [PagoClienteController::class, 'consultar'])
            ->name('pagos.consultar');
    });



   // ====================
// TRABAJADOR
// ====================
Route::prefix('trabajador')
    ->middleware('auth')
    ->name('trabajador.')
    ->group(function () {
        // /trabajador/dashboard  → nombre: trabajador.dashboard
        Route::get('/dashboard', [TrabajadorDashboard::class, 'index'])
            ->name('dashboard');

            // NUEVO: listar pagos recibidos (JSON)
        Route::get('/pagos-recibidos', [DashboardController::class, 'pagosRecibidos'])
            ->name('pagos.recibidos');

       

        // /trabajador/cambiar-password → nombre: trabajador.cambiar-password
        Route::post('/cambiar-password', [TrabajadorDashboard::class, 'cambiarPassword'])
            ->name('cambiar-password');

                // subir / actualizar QR
        Route::post('/qr-actualizar', [TrabajadorDashboard::class, 'actualizarQr'])
            ->name('qr.actualizar');

        // ❗ eliminar QR
        Route::delete('/qr-eliminar', [TrabajadorDashboard::class, 'eliminarQr'])
            ->name('qr.eliminar');

    });


// ====================
// PROVEEDOR
// ====================
Route::prefix('proveedor')
    ->middleware('auth')
    ->name('proveedor.')
    ->group(function () {
        // /proveedor/dashboard → nombre: proveedor.dashboard
        Route::get('/dashboard', [ProveedorDashboardController::class, 'index'])
            ->name('dashboard');

        // /proveedor/cambiar-password → nombre: proveedor.cambiar-password
        Route::post('/cambiar-password', [ProveedorDashboardController::class, 'cambiarPassword'])
            ->name('cambiar-password');

        // /proveedor/compras → nombre: proveedor.compras.index
        Route::get('/compras', [ProveedorCompraController::class, 'index'])
            ->name('compras.index');

        // /proveedor/compras/pendientes → nombre: proveedor.compras.pendientes
        Route::get('/compras/pendientes', [ProveedorCompraController::class, 'pendientes'])
            ->name('compras.pendientes');

        // /proveedor/compras/{id} → nombre: proveedor.compras.actualizar
        Route::put('/compras/{id}', [ProveedorCompraController::class, 'actualizar'])
            ->name('compras.actualizar');
                    // subir / actualizar QR
        Route::post('/qr-actualizar', [ProveedorDashboardController::class, 'actualizarQr'])
            ->name('qr.actualizar');

              Route::post('/cambiar-password', [ProveedorDashboardController::class, 'cambiarPassword'])
            ->name('cambiar-password');
    });






    // ====================
    // PROPIETARIO - CONSTRUCCIÓN (DASHBOARD)
    // ====================
    Route::get('/Propietario/Construccion', function () {
        /** @var \App\Models\Usuario $u */
        $u = Auth::user();

        if (!$u) {
            return redirect()->route('login');
        }

        if ($u->role !== 'propietario') {
            Auth::logout();
            return redirect()->route('login');
        }

        $u->load('propietario');

        $prop = $u->propietario;
        $especialidad = strtolower($prop->especialidad_propietario ?? '');

        if (strpos($especialidad, 'construcción') === false &&
            strpos($especialidad, 'construccion') === false) {
            Auth::logout();
            return redirect()->route('login');
        }

        return Inertia::render('Propietario/Construccion/Dashboard', [
            'user' => [
                'id_usuario'  => $u->id_usuario,
                'name'        => $u->name,
                'propietario' => $prop,
            ],
        ]);
    })->name('propietario.construccion.dashboard');

    // ====================
    // PROPIETARIO - MELAMINA (DASHBOARD)
    // ====================
    Route::get('/Propietario/melamina', function () {
        /** @var \App\Models\Usuario $u */
        $u = Auth::user();

        if (!$u) {
            return redirect()->route('login');
        }

        if ($u->role !== 'propietario') {
            Auth::logout();
            return redirect()->route('login');
        }

        $u->load('propietario');

        $prop = $u->propietario;
        $especialidad = strtolower($prop->especialidad_propietario ?? '');

        if (strpos($especialidad, 'melamina') === false) {
            Auth::logout();
            return redirect()->route('login');
        }

        return Inertia::render('Propietario/melamina/Dashboard', [
            'user' => [
                'id_usuario'  => $u->id_usuario,
                'name'        => $u->name,
                'propietario' => $prop,
            ],
        ]);
    })->name('propietario.melamina.dashboard');

    // ====================
    // GRUPO MELAMINA
    // ====================
    Route::prefix('propietario/melamina')
        ->name('propietario.melamina.')
        ->group(function () {

            // PROYECTOS
            Route::get('/proyectos', [ProyectoController::class, 'index']);
            Route::post('/proyectos', [ProyectoController::class, 'store']);
            Route::post('/proyectos/{id}', [ProyectoController::class, 'update']);
            Route::delete('/proyectos/{id}', [ProyectoController::class, 'destroy']);

            // ASIGNACIONES
            Route::get('/asignaciones', [AsignacionController::class, 'index']);
            Route::post('/asignaciones', [AsignacionController::class, 'store']);
            Route::post('/asignaciones/{id}', [AsignacionController::class, 'update']);
            Route::delete('/asignaciones/{id}', [AsignacionController::class, 'destroy']);

            // CLIENTES
            Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
            Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');

            // USUARIOS
            Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios');
            Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
            Route::put('/usuarios/{id_usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
            Route::patch('/usuarios/{id_usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.estado');
            Route::delete('/usuarios/{id_usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

            // PERFIL
            Route::get('/perfil', [PerfilController::class, 'show'])->name('propietario.perfil.show');
            Route::post('/perfil', [PerfilController::class, 'update'])->name('propietario.perfil.update');

            // TRABAJADORES
            Route::get('/trabajadores', [TrabajadorController::class, 'index'])->name('trabajadores');
            Route::post('/trabajadores', [TrabajadorController::class, 'store'])->name('trabajadores.store');
            Route::post('/trabajadores/{id}', [TrabajadorController::class, 'update'])->name('trabajadores.update');
            Route::delete('/trabajadores/{id}', [TrabajadorController::class, 'destroy'])->name('trabajadores.destroy');

            // PROVEEDORES
            Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores');
            Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
            Route::post('/proveedores/{id}', [ProveedorController::class, 'update'])->name('proveedores.update');
            Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

            // COMPRAS
            Route::get('/compras', [CompraController::class, 'index']);
            Route::post('/compras', [CompraController::class, 'store']);
             // 🔹 DETALLES DE COMPRA 
            Route::get('/compras/{id_compra}/detalles', [DetalleCompraController::class, 'index']);
          Route::post('/compras/detalles', [DetalleCompraController::class, 'store']);

            Route::post('/compras/{id}', [CompraController::class, 'update']);
            Route::delete('/compras/{id}', [CompraController::class, 'destroy']);

          


            // PRODUCTOS
            Route::get('/productos', [ProductoController::class, 'index'])->name('productos');
            Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
            Route::post('/productos/{id}', [ProductoController::class, 'update'])->name('productos.update');
            Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('productos.destroy');

            // CATEGORÍAS
            Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias');
            Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
            Route::post('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
            Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

            // SERVICIOS
            Route::get('/servicios', [ServicioController::class, 'index']);
            Route::post('/servicios', [ServicioController::class, 'store']);
            Route::post('/servicios/{id}', [ServicioController::class, 'update']);
            Route::delete('/servicios/{id}', [ServicioController::class, 'destroy']);

            // PROYECTO_SERVICIOS
            Route::get('/proyecto-servicios', [ProyectoServicioController::class, 'index']);
            Route::post('/proyecto-servicios', [ProyectoServicioController::class, 'store']);
            Route::post('/proyecto-servicios/{id}', [ProyectoServicioController::class, 'update']);
            Route::delete('/proyecto-servicios/{id}', [ProyectoServicioController::class, 'destroy']);

            // INVENTARIO
            Route::get('/inventario', [InventarioController::class, 'index']);
            Route::post('/inventario', [InventarioController::class, 'store']);
            Route::post('/inventario/{id}', [InventarioController::class, 'update']);
            Route::delete('/inventario/{id}', [InventarioController::class, 'destroy']);

            // UNIDAD DE MEDIDA
            Route::get('/unidad-medida', [UnidadMedidaController::class, 'index']);
            Route::post('/unidad-medida', [UnidadMedidaController::class, 'store']);
            Route::post('/unidad-medida/{id}', [UnidadMedidaController::class, 'update']);
            Route::delete('/unidad-medida/{id}', [UnidadMedidaController::class, 'destroy']);
           
             //REPORTES
             Route::get('/reportes/pagos',[ReportePagoController::class, 'index'])->name('reportes.pagos');
            // PAGOS (propietario)
            Route::prefix('pagos')->group(function () {
                Route::get('/', [PagoController::class, 'index']);
                Route::post('/', [PagoController::class, 'store']);
                Route::get('/proveedores', [PagoController::class, 'listarProveedores']);
                Route::get('/trabajadores', [PagoController::class, 'listarTrabajadores']);
                Route::get('/cuotas-pendientes', [PagoController::class, 'cuotasPendientes']);

                Route::post('/consultar', [PagoController::class, 'consultar']);
                Route::post('/pagofacil/callback', [PagoController::class, 'callbackPagofacil']);
            });

            // ESTADÍSTICAS DASHBOARD MELAMINA
            Route::prefix('dashboard')->group(function () {
                Route::get('/resumen-personas',   [DashboardController::class, 'resumenPersonas']);
                Route::get('/inventario-mensual', [DashboardController::class, 'inventarioMensual']);
                Route::get('/pagos-pendientes',   [DashboardController::class, 'pagosPendientes']);
                Route::get('/proyectos-contador', [DashboardController::class, 'proyectosContador']);
               Route::get('/cuotas-pendientes',[DashboardController::class, 'cuotasPendientesGeneral']);
              Route::get('/proyectos-pagos-clientes',[DashboardController::class, 'proyectosPagosClientes']);

            });
        });

    // ======================================================
    // GRUPO CONSTRUCCIÓN
    // ======================================================
    Route::prefix('propietario/Construccion')
        ->name('propietario.construccion.')
        ->group(function () {

          // PROYECTOS
            Route::get('/proyectos', [ProyectoController::class, 'index']);
            Route::post('/proyectos', [ProyectoController::class, 'store']);
            Route::post('/proyectos/{id}', [ProyectoController::class, 'update']);
            Route::delete('/proyectos/{id}', [ProyectoController::class, 'destroy']);

            // ASIGNACIONES
            Route::get('/asignaciones', [AsignacionController::class, 'index']);
            Route::post('/asignaciones', [AsignacionController::class, 'store']);
            Route::post('/asignaciones/{id}', [AsignacionController::class, 'update']);
            Route::delete('/asignaciones/{id}', [AsignacionController::class, 'destroy']);

            // CLIENTES
            Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
            Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');

            // USUARIOS
            Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios');
            Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
            Route::put('/usuarios/{id_usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
            Route::patch('/usuarios/{id_usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.estado');
            Route::delete('/usuarios/{id_usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

            // PERFIL
            Route::get('/perfil', [PerfilController::class, 'show'])->name('propietario.perfil.show');
            Route::post('/perfil', [PerfilController::class, 'update'])->name('propietario.perfil.update');

            // TRABAJADORES
            Route::get('/trabajadores', [TrabajadorController::class, 'index'])->name('trabajadores');
            Route::post('/trabajadores', [TrabajadorController::class, 'store'])->name('trabajadores.store');
            Route::post('/trabajadores/{id}', [TrabajadorController::class, 'update'])->name('trabajadores.update');
            Route::delete('/trabajadores/{id}', [TrabajadorController::class, 'destroy'])->name('trabajadores.destroy');

            // PROVEEDORES
            Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores');
            Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
            Route::post('/proveedores/{id}', [ProveedorController::class, 'update'])->name('proveedores.update');
            Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

            // COMPRAS
            Route::get('/compras', [CompraController::class, 'index']);
            Route::post('/compras', [CompraController::class, 'store']);
             // 🔹 DETALLES DE COMPRA 
            Route::get('/compras/{id_compra}/detalles', [DetalleCompraController::class, 'index']);
          Route::post('/compras/detalles', [DetalleCompraController::class, 'store']);

            Route::post('/compras/{id}', [CompraController::class, 'update']);
            Route::delete('/compras/{id}', [CompraController::class, 'destroy']);

          


            // PRODUCTOS
            Route::get('/productos', [ProductoController::class, 'index'])->name('productos');
            Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
            Route::post('/productos/{id}', [ProductoController::class, 'update'])->name('productos.update');
            Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('productos.destroy');

            // CATEGORÍAS
            Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias');
            Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
            Route::post('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
            Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

            // SERVICIOS
            Route::get('/servicios', [ServicioController::class, 'index']);
            Route::post('/servicios', [ServicioController::class, 'store']);
            Route::post('/servicios/{id}', [ServicioController::class, 'update']);
            Route::delete('/servicios/{id}', [ServicioController::class, 'destroy']);

            // PROYECTO_SERVICIOS
            Route::get('/proyecto-servicios', [ProyectoServicioController::class, 'index']);
            Route::post('/proyecto-servicios', [ProyectoServicioController::class, 'store']);
            Route::post('/proyecto-servicios/{id}', [ProyectoServicioController::class, 'update']);
            Route::delete('/proyecto-servicios/{id}', [ProyectoServicioController::class, 'destroy']);

            // INVENTARIO
            Route::get('/inventario', [InventarioController::class, 'index']);
            Route::post('/inventario', [InventarioController::class, 'store']);
            Route::post('/inventario/{id}', [InventarioController::class, 'update']);
            Route::delete('/inventario/{id}', [InventarioController::class, 'destroy']);

            // UNIDAD DE MEDIDA
            Route::get('/unidad-medida', [UnidadMedidaController::class, 'index']);
            Route::post('/unidad-medida', [UnidadMedidaController::class, 'store']);
            Route::post('/unidad-medida/{id}', [UnidadMedidaController::class, 'update']);
            Route::delete('/unidad-medida/{id}', [UnidadMedidaController::class, 'destroy']);
           
             //REPORTES
             Route::get('/reportes/pagos',[ReportePagoController::class, 'index'])->name('reportes.pagos');
            // PAGOS (propietario)
            Route::prefix('pagos')->group(function () {
                Route::get('/', [PagoController::class, 'index']);
                Route::post('/', [PagoController::class, 'store']);
                Route::get('/proveedores', [PagoController::class, 'listarProveedores']);
                Route::get('/trabajadores', [PagoController::class, 'listarTrabajadores']);
                Route::get('/cuotas-pendientes', [PagoController::class, 'cuotasPendientes']);

                Route::post('/consultar', [PagoController::class, 'consultar']);
                Route::post('/pagofacil/callback', [PagoController::class, 'callbackPagofacil']);
            });

            // ESTADÍSTICAS DASHBOARD MELAMINA
            Route::prefix('dashboard')->group(function () {
                Route::get('/resumen-personas',   [DashboardController::class, 'resumenPersonas']);
                Route::get('/inventario-mensual', [DashboardController::class, 'inventarioMensual']);
                Route::get('/pagos-pendientes',   [DashboardController::class, 'pagosPendientes']);
                Route::get('/proyectos-contador', [DashboardController::class, 'proyectosContador']);
               Route::get('/cuotas-pendientes',[DashboardController::class, 'cuotasPendientesGeneral']);
              Route::get('/proyectos-pagos-clientes',[DashboardController::class, 'proyectosPagosClientes']);

            });
        });

}); // <-- cierre del middleware auth

Route::match(['GET', 'POST'], 'cliente/pagos/payment/callback', [
    PagoClienteController::class,
    'callbackPagofacil'
])->name('cliente.pagos.pagofacil.callback');

