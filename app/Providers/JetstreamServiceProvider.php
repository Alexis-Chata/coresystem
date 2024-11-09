<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use App\Models\Empleado;
use App\Models\UserEmpleado;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::deleteUsersUsing(DeleteUser::class);

        Vite::prefetch(concurrency: 3);

        Fortify::registerView(function () {
            $empleados_id_asignados = UserEmpleado::where('tipo', 'main')->get()->pluck('empleado_id');
            $empleados_id_sin_asignar = Empleado::whereNotIn('id', $empleados_id_asignados)->get();
            //dd($empleados_id_asignados, $empleados_id_sin_asignar);
            return view('auth.register', compact('empleados_id_sin_asignar'));
        });
    }

    /**
     * Configure the permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
