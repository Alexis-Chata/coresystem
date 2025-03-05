<aside :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
    class="absolute left-0 top-0 z-9999 flex h-screen w-72.5 flex-col overflow-y-hidden bg-black duration-300 ease-linear dark:bg-boxdark lg:static lg:translate-x-0"
    @click.outside="sidebarToggle = false">
    <!-- SIDEBAR HEADER -->
    <div class="flex items-center justify-between gap-2 px-6 py-5.5 lg:py-6.5">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('src/images/logo/logo.svg') }}" alt="Logo" />
        </a>

        <button class="block lg:hidden" @click.stop="sidebarToggle = !sidebarToggle">
            <x-svg_back_arrow />
        </button>
    </div>
    <!-- SIDEBAR HEADER -->

    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <!-- Sidebar Menu -->
        <nav class="mt-5 px-4 py-4 lg:mt-9 lg:px-6" x-data="{ selected: $persist('Dashboard') }">
            <!-- Menu Group -->
            <div>
                <h3 class="mb-4 ml-4 text-sm font-medium text-bodydark2">MENU</h3>

                <ul class="mb-6 flex flex-col gap-1.5">
                    <!-- Menu Item Dashboard -->
                    <!-- Dashboard -->
                    <li>
                        <a class="group relative flex items-center gap-2.5 rounded-sm px-4 py-2 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                            href="{{ route('dashboard') }}"
                            :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('dashboard') }}' }">
                            <x-svg_grid_2x2 />
                            Dashboard
                        </a>
                    </li>

                    <!-- Gestión de Marcas -->
                    @can('view marca')
                        <li>
                            <a href="{{ route('marcas.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('marcas.index') }}' }">
                                <x-svg_calendar />
                                Marcas
                            </a>
                        </li>
                    @endcan

                    <!-- Cliente -->
                    @can('view cliente')
                        <li>
                            <a href="{{ route('cliente.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('cliente.index') }}' }">
                                <x-svg_agenda />
                                Cliente
                            </a>
                        </li>
                    @endcan

                    <!-- Producto -->
                    @can('view producto')
                        <li x-data="{ open: {{ request()->routeIs('producto.*') ? 'true' : 'false' }} }">
                            <a class="group relative flex items-center gap-2.5 rounded-sm px-4 py-2 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                href="#" @click.prevent="open = !open"
                                :class="{ 'bg-graydark dark:bg-meta-4': open }">
                                <x-svg_user />
                                Productos
                                <x-svg_desplegable />
                            </a>

                            <!-- Dropdown Menu -->
                            <div class="translate transform overflow-hidden" x-show="open" x-transition>
                                <ul class="mb-5.5 mt-4 flex flex-col gap-2.5 pl-6">
                                    @can('edit producto')
                                        <li>
                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                href="{{ route('producto.index') }}"
                                                :class="{ 'text-white': {{ request()->routeIs('producto.index') ? 'true' : 'false' }} }">
                                                Lista de Productos
                                            </a>
                                        </li>
                                    @endcan
                                    <li>
                                        <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                            href="{{ route('producto.precios-mayorista') }}"
                                            :class="{ 'text-white': {{ request()->routeIs('producto.precios-mayorista') ? 'true' : 'false' }} }">
                                            Precios Mayorista
                                        </a>
                                    </li>
                                    <li>
                                        <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                            href="{{ route('producto.precios-bodega') }}"
                                            :class="{ 'text-white': {{ request()->routeIs('producto.precios-bodega') ? 'true' : 'false' }} }">
                                            Precios Bodega
                                        </a>
                                    </li>
                                    @can('stock producto')
                                        <li>
                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                href="{{ route('producto.stock') }}"
                                                :class="{ 'text-white': {{ request()->routeIs('producto.stock') ? 'true' : 'false' }} }">
                                                Stock de Productos
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                    @endcan

                    <!-- Pedido -->
                    @can('view pedido')
                        <li>
                            <a href="{{ route('pedido.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('pedido.index') }}' }">
                                <x-svg_tecla />
                                Pedido
                            </a>
                        </li>
                    @endcan

                    <!-- Categoría -->
                    @can('view categoria')
                        <li>
                            <a href="{{ route('categoria.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('categoria.index') }}' }">
                                <x-svg_grid_2x2 />
                                Categoría
                            </a>
                        </li>
                    @endcan

                    <!-- Proveedor -->
                    @can('view proveedor')
                        <li>
                            <a href="{{ route('proveedor.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('proveedor.index') }}' }">
                                <x-svg_user />
                                Proveedor
                            </a>
                        </li>
                    @endcan

                    <!-- Empleado -->
                    @can('view empleado')
                        <li>
                            <a href="{{ route('empleado.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('empleado.index')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_user />
                                Empleados
                            </a>
                        </li>
                    @endcan

                    <!-- Elemento de menú Ruta -->
                    @can('view ruta')
                        <li>
                            <a href="{{ route('ruta.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('ruta.index')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_carrito />
                                Ruta
                            </a>
                        </li>
                    @endcan

                    <!-- Elemento de menú Padron -->
                    @can('view padron')
                        <li>
                            <a href="{{ route('padron.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('padron.index')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_carrito />
                                Padron
                            </a>
                        </li>
                    @endcan

                    <!-- Movimiento -->
                    @can('view movimiento')
                        <li x-data="{ open: {{ request()->routeIs('movimiento.*') ? 'true' : 'false' }} }">
                            <a class="group relative flex items-center gap-2.5 rounded-sm px-4 py-2 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                href="#" @click.prevent="open = !open"
                                :class="{ 'bg-graydark dark:bg-meta-4': open }">
                                <x-svg_grid_add />
                                Movimientos
                                <x-svg_desplegable />
                            </a>

                            <!-- Dropdown Menu -->
                            <div class="translate transform overflow-hidden" x-show="open" x-transition>
                                <ul class="mb-5.5 mt-4 flex flex-col gap-2.5 pl-6">
                                    @can('create movimiento')
                                        <li>
                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                href="{{ route('movimiento.create') }}"
                                                :class="{ 'text-white': {{ request()->routeIs('movimiento.create') ? 'true' : 'false' }} }">
                                                Ingresar Movimiento
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view movimiento')
                                        <li>
                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                href="{{ route('movimiento.view') }}"
                                                :class="{ 'text-white': {{ request()->routeIs('movimiento.view') ? 'true' : 'false' }} }">
                                                Ver Movimientos
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                    @endcan

                    <!-- Asignar Pedidos -->
                    @can('asignar pedido')
                        <li>
                            <a href="{{ route('pedido.asignar') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('pedido.asignar')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_companys />
                                Asignar Pedidos
                            </a>
                        </li>
                    @endcan

                    <!-- Generar movimiento o Generar Carga -->
                    @can('generar-movimientoliq movimiento')
                        <li>
                            <a href="{{ route('movimiento.generar-movimientoliq') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('movimiento.generar-movimientoliq')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_companys />
                                Generar Movimiento Liquido-Conductor
                            </a>
                        </li>
                    @endcan

                    <!-- Generar Comprobantes -->
                    @can('create comprobante')
                        <li>
                            <a href="{{ route('comprobantes.create') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('comprobantes.create')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_companys />
                                Generar Comprobantes
                            </a>
                        </li>
                    @endcan

                    <!-- Imprimir Comprobantes -->
                    @can('view comprobante')
                        <li>
                            <a href="{{ route('comprobantes.imprimir') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('comprobantes.imprimir')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_companys />
                                Imprimir Comprobantes
                            </a>
                        </li>
                    @endcan

                    <!-- Envio Comprobantes -->
                    @can('envio comprobante')
                        <li>
                            <a href="{{ route('comprobantes.envio') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('comprobantes.envio')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_companys />
                                Envio Comprobantes
                            </a>
                        </li>
                    @endcan

                    <!-- Envio Comprobantes -->
                    @can('envio-guias comprobante')
                        <li>
                            <a href="{{ route('guias.envio') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('guias.envio')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_companys />
                                Envio Guias
                            </a>
                        </li>
                    @endcan

                    <!-- Empresa -->
                    @can('view empresa')
                        <li>
                            <a href="{{ route('empresa.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('empresa.index')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_companys />

                                Empresas
                            </a>
                        </li>
                    @endcan

                    <!-- reporte -->
                    @can('view reporte')
                        <li>
                            <a href="{{ route('reporte.view') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4 @if (request()->routeIs('reporte.view')) bg-graydark dark:bg-meta-4 @endif">
                                <x-svg_companys />

                                Reportes
                            </a>
                        </li>
                    @endcan
                    <!-- Menu Item Settings -->
                </ul>
            </div>

            <!-- Others Group -->
            <div>
                <h3 class="mb-4 ml-4 text-sm font-medium text-bodydark2">OTROS</h3>

                <ul class="mb-6 flex flex-col gap-1.5">
                    <!-- Menu Item Chart -->

                    <!-- Gestión de Usuarios -->
                    @can('view usuarios')
                        <li>
                            <a href="{{ route('user.lista') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('user.lista') }}' }">
                                <x-svg_user />
                                Gestión de Usuarios
                            </a>
                        </li>
                    @endcan

                    <!-- Gestión de Roles -->
                    @can('view roles')
                        <li>
                            <a href="{{ route('user-roles.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('user-roles.index') }}' }">
                                <x-svg_user />
                                Gestión de Roles
                            </a>
                        </li>
                    @endcan

                    <!-- Gestión de Roles -->
                    @can('view roles')
                        <li>
                            <a href="{{ route('permisos.usuario') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('permisos.usuario') }}' }">
                                <x-svg_user />
                                Permisos de Usuario
                            </a>
                        </li>
                    @endcan

                    <!-- Gestión de Roles -->
                    @can('view roles')
                        <li>
                            <a href="{{ route('permisos.roles') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('permisos.roles') }}' }">
                                <x-svg_user />
                                Permisos de Roles
                            </a>
                        </li>
                    @endcan

                </ul>
            </div>
        </nav>
        <!-- Sidebar Menu -->

    </div>
</aside>
