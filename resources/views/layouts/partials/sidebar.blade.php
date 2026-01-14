<aside :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
    class="absolute left-0 top-0 z-40 flex h-screen w-72.5 flex-col overflow-y-hidden bg-black duration-300 ease-linear dark:bg-boxdark lg:static lg:translate-x-0"
    @click.outside="sidebarToggle = false">
    <!-- SIDEBAR HEADER -->
    <div class="flex items-center justify-between gap-2 px-6 py-6 lg:py-6.5">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset(app()->environment('local') ? 'src/images/logo/logo-local.svg' : 'src/images/logo/logo.svg') }}"
                alt="Logo" />
        </a>

        <button class="block lg:hidden" @click.stop="sidebarToggle = !sidebarToggle">
            <x-svg_back_arrow />
        </button>
    </div>
    <!-- SIDEBAR HEADER -->

    @php
        $grupos_links = [
            [
                'grupo_descripcion' => 'MENU', //opcional
                'grupo_name' => 'MENU',
                'links' => [
                    [
                        'link_descripcion' => 'Dashboard', //opcional
                        'permission' => 'view dashboard',
                        'route' => 'dashboard',
                        'icon' => 'svg_grid_2x2', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Dashboard',
                    ],
                    [
                        'link_descripcion' => 'Gestión de Marcas', //opcional
                        'permission' => 'view marca',
                        'route' => 'marcas.index',
                        'icon' => 'svg_calendar', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Marcas',
                    ],
                    [
                        'link_descripcion' => 'Gestión de Marcas', //opcional
                        'permission' => 'view cliente',
                        'route' => 'cliente.index',
                        'icon' => 'svg_agenda', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Cliente',
                    ],
                    [
                        'link_descripcion' => 'Gestión de Producto', //opcional
                        'permission' => 'view producto',
                        'route' => 'producto.*',
                        'icon' => 'svg_user', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Productos', // Productos
                        'sublinks' => [
                            [
                                'permission' => 'edit producto',
                                'route' => 'producto.index',
                                'name' => 'Lista de Productos',
                            ],
                            [
                                'permission' => 'mayorista precios',
                                'route' => 'producto.precios-mayorista',
                                'name' => 'Precios Mayorista',
                            ],
                            [
                                'permission' => 'bodega precios',
                                'route' => 'producto.precios-bodega',
                                'name' => 'Precios Bodega',
                            ],
                            [
                                'permission' => 'stock producto',
                                'route' => 'producto.stock',
                                'name' => 'Stock de Productos',
                            ],
                            [
                                'permission' => 'precio-bm producto',
                                'route' => 'producto.precio-bm',
                                'name' => 'Precios B/M',
                            ],
                        ],
                    ],
                    [
                        'link_descripcion' => 'Gestión de movimientos', //opcional
                        'permission' => 'view movimiento',
                        'route' => 'movimiento.*',
                        'icon' => 'svg_grid_add', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Movimientos', // Productos
                        'sublinks' => [
                            [
                                'permission' => 'create movimiento',
                                'route' => 'movimiento.create',
                                'name' => 'Ingresar Movimiento',
                            ],
                            [
                                'permission' => 'view movimiento',
                                'route' => 'movimiento.view',
                                'name' => 'Ver Movimientos',
                            ],
                        ],
                    ],
                    [
                        'link_descripcion' => 'Pedido', //opcional
                        'permission' => 'view pedido',
                        'route' => 'pedido.index',
                        'icon' => 'svg_tecla', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Pedido',
                    ],
                    [
                        'link_descripcion' => 'Categoría', //opcional
                        'permission' => 'view categoria',
                        'route' => 'categoria.index',
                        'icon' => 'svg_grid_2x2', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Categoría',
                    ],
                    [
                        'link_descripcion' => 'Proveedor', //opcional
                        'permission' => 'view proveedor',
                        'route' => 'proveedor.index',
                        'icon' => 'svg_user', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Proveedor',
                    ],
                    [
                        'link_descripcion' => 'Empleado', //opcional
                        'permission' => 'view empleado',
                        'route' => 'empleado.index',
                        'icon' => 'svg_user', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Empleados',
                    ],
                    [
                        'link_descripcion' => 'Vehículo', //opcional
                        'permission' => 'view vehiculo',
                        'route' => 'vehiculo.index',
                        'icon' => 'svg_carrito', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Vehículos',
                    ],
                    [
                        'link_descripcion' => 'Ruta', //opcional
                        'permission' => 'view ruta',
                        'route' => 'ruta.index',
                        'icon' => 'svg_carrito', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Rutas',
                    ],
                    [
                        'link_descripcion' => 'Padron', //opcional
                        'permission' => 'view padron',
                        'route' => 'padron.index',
                        'icon' => 'svg_carrito', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Padrons',
                    ],
                    [
                        'link_descripcion' => 'Asignar Pedido', //opcional
                        'permission' => 'asignar pedido',
                        'route' => 'pedido.asignar',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Asignar Pedidos',
                    ],
                    [
                        'link_descripcion' => 'Generar movimiento o Generar Carga', //opcional
                        'permission' => 'generar-movimientoliq movimiento',
                        'route' => 'movimiento.generar-movimientoliq',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Generar Movimiento Liquido-Conductor',
                    ],
                    [
                        'link_descripcion' => 'Generar Comprobantes', //opcional
                        'permission' => 'create comprobante',
                        'route' => 'comprobantes.create',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Generar Comprobantes',
                    ],
                    [
                        'link_descripcion' => 'Imprimir Comprobantes', //opcional
                        'permission' => 'view comprobante',
                        'route' => 'comprobantes.imprimir',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Imprimir Comprobantes',
                    ],
                    [
                        'link_descripcion' => 'Envio Comprobantes', //opcional
                        'permission' => 'envio comprobante',
                        'route' => 'comprobantes.envio',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Envio Comprobantes',
                    ],
                    [
                        'link_descripcion' => 'Envio Guias', //opcional
                        'permission' => 'envio-guias comprobante',
                        'route' => 'guias.envio',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Envio Guias',
                    ],
                    [
                        'link_descripcion' => 'Empresa', //opcional
                        'permission' => 'view empresa',
                        'route' => 'empresa.index',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Empresas',
                    ],
                    [
                        'link_descripcion' => 'Reporte', //opcional
                        'permission' => 'view reporte',
                        'route' => 'reporte.view',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Reportes',
                    ],
                    [
                        'link_descripcion' => 'Liquidaciones', //opcional
                        'permission' => 'view liquidacion',
                        'route' => 'liquidacion.view',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Liquidaciones',
                    ],
                    [
                        'link_descripcion' => 'Avances', //opcional
                        'permission' => 'view avance',
                        'route' => 'avance.view',
                        'icon' => 'svg_companys', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Avances',
                    ],
                ],
            ],

            [
                'grupo_descripcion' => 'OTROS', //opcional
                'grupo_name' => 'OTROS',
                'links' => [
                    [
                        'link_descripcion' => 'Gestión de Usuarios', //opcional
                        'permission' => 'view usuarios',
                        'route' => 'user.lista',
                        'icon' => 'svg_user', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Gestión de Usuarios',
                    ],
                    [
                        'link_descripcion' => 'Gestión de Roles', //opcional
                        'permission' => 'view roles',
                        'route' => 'user-roles.index',
                        'icon' => 'svg_user', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Gestión de Roles',
                    ],
                    [
                        'link_descripcion' => 'Gestión de Permisos', //opcional
                        'permission' => 'view roles',
                        'route' => 'permisos.usuario',
                        'icon' => 'svg_user', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Permisos de Usuario',
                    ],
                    [
                        'link_descripcion' => 'Gestión de Permisos', //opcional
                        'permission' => 'view roles',
                        'route' => 'permisos.roles',
                        'icon' => 'svg_user', // icono es un componente blade simple <x-svg_user />
                        'name' => 'Permisos de Roles',
                    ],
                ],
            ],
        ];
    @endphp

    @php
        $user = auth()->user();

        // Evalúa un permiso o una lista de permisos con modo any/all
        $canPerm = function ($perm, string $mode = 'any') use ($user) {
            // Si no definiste permiso, lo dejamos visible (útil para links públicos)
            if ($perm === null || $perm === '' || $perm === []) {
                return true;
            }

            // Normalizamos a array
            $perms = is_array($perm) ? $perm : [$perm];
            $perms = array_values(array_filter($perms, fn($p) => !empty($p)));

            if (count($perms) === 0) {
                return true;
            }

            return $mode === 'all'
                ? collect($perms)->every(fn($p) => $user?->can($p)) // TODOS
                : collect($perms)->contains(fn($p) => $user?->can($p)); // CUALQUIERA
        };

        // Evalúa un item del menú. Si tiene sublinks, se muestra si:
        // - el padre pasa, O
        // - al menos un sublink pasa
        $canItem = function (array $item) use ($canPerm) {
            $allowedParent = $canPerm($item['permission'] ?? null, $item['perm_mode'] ?? 'any');

            $sublinks = $item['sublinks'] ?? [];
            if (!empty($sublinks)) {
                $allowedAnySub = collect($sublinks)->contains(
                    fn($s) => $canPerm($s['permission'] ?? null, $s['perm_mode'] ?? 'any'),
                );
                return $allowedParent || $allowedAnySub;
            }

            return $allowedParent;
        };
    @endphp

    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <!-- Sidebar Menu -->
        <nav class="mt-2 px-4 py-4 lg:px-6">
            @foreach ($grupos_links as $grupo_link)
                <!-- Grupo {{ $grupo_link['grupo_descripcion'] ?? '' }} -->
                <div>
                    <h3 class="mb-4 ml-4 text-sm font-medium text-bodydark2">{{ $grupo_link['grupo_name'] }}</h3>

                    <ul class="mb-6 flex flex-col gap-1.5">
                        <!-- Menu Item {{ $grupo_link['grupo_name'] }} -->
                        @foreach ($grupo_link['links'] as $item)
                            @if ($canItem($item))
                                <!-- {{ $item['link_descripcion'] ?? '' }} -->
                                @if (!count($item['sublinks'] ?? []))
                                    <li>
                                        <a class="group relative flex items-center gap-2.5 rounded-sm px-4 py-2 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                            :class="{ 'bg-graydark dark:bg-meta-4': {{ request()->routeIs($item['route']) ? 'true' : 'false' }} }"
                                            href="{{ route($item['route']) }}">
                                            <x-dynamic-component :component="$item['icon']" />
                                            {{ $item['name'] }}
                                        </a>
                                    </li>
                                @else
                                    <li x-data="{ open: {{ request()->routeIs($item['route']) ? 'true' : 'false' }} }">
                                        <a class="group relative flex items-center gap-2.5 rounded-sm px-4 py-2 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                            href="#" @click.prevent="open = !open"
                                            :class="{ 'bg-graydark dark:bg-meta-4': open }">
                                            <x-dynamic-component :component="$item['icon']" />
                                            {{ $item['name'] }}
                                            <x-svg_desplegable />
                                        </a>

                                        <!-- Dropdown Menu -->
                                        <div class="translate transform overflow-hidden" x-show="open" x-transition>
                                            <ul class="mb-5.5 mt-4 flex flex-col gap-2.5 pl-6">
                                                @foreach ($item['sublinks'] as $sublink)
                                                    @if ($canPerm($sublink['permission'] ?? null, $sublink['perm_mode'] ?? 'any'))
                                                        <li>
                                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                                href="{{ route($sublink['route']) }}"
                                                                :class="{ 'text-white': {{ request()->routeIs($sublink['route']) ? 'true' : 'false' }} }">
                                                                {{ $sublink['name'] }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                        <!-- Menu Item {{ $grupo_link['grupo_name'] }} -->
                    </ul>
                </div>
            @endforeach
        </nav>
        <!-- Sidebar Menu -->
    </div>
</aside>
