<aside :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
    class="absolute left-0 top-0 z-9999 flex h-screen w-72.5 flex-col overflow-y-hidden bg-black duration-300 ease-linear dark:bg-boxdark lg:static lg:translate-x-0"
    @click.outside="sidebarToggle = false">
    <!-- SIDEBAR HEADER -->
    <div class="flex items-center justify-between gap-2 px-6 py-5.5 lg:py-6.5">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('src/images/logo/logo.svg') }}" alt="Logo" />
        </a>

        <button class="block lg:hidden" @click.stop="sidebarToggle = !sidebarToggle">
            <svg class="fill-current" width="20" height="18" viewBox="0 0 20 18" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M19 8.175H2.98748L9.36248 1.6875C9.69998 1.35 9.69998 0.825 9.36248 0.4875C9.02498 0.15 8.49998 0.15 8.16248 0.4875L0.399976 8.3625C0.0624756 8.7 0.0624756 9.225 0.399976 9.5625L8.16248 17.4375C8.31248 17.5875 8.53748 17.7 8.76248 17.7C8.98748 17.7 9.17498 17.625 9.36248 17.475C9.69998 17.1375 9.69998 16.6125 9.36248 16.275L3.02498 9.8625H19C19.45 9.8625 19.825 9.4875 19.825 9.0375C19.825 8.55 19.45 8.175 19 8.175Z"
                    fill="" />
            </svg>
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
                            href="{{ route('dashboard') }}" @click="selected = 'Dashboard'"
                            :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('dashboard') }}' }">
                            <x-svg_grid_2x2 />
                            Dashboard
                        </a>
                    </li>

                    <!-- Gestión de Marcas -->
                    @can('view marca')
                        <li>
                            <a href="{{ route('marcas.index') }}" @click="selected = 'Marcas'"
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
                            <a href="{{ route('cliente.index') }}" @click="selected = 'Cliente'"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('cliente.index') }}' }">
                                <x-svg_agenda />
                                Cliente
                            </a>
                        </li>
                    @endcan

                    <!-- Producto -->
                    @can('view producto')
                        <li>
                            <a class="group relative flex items-center gap-2.5 rounded-sm px-4 py-2 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                href="#" @click.prevent="selected = (selected === 'Productos' ? '':'Productos')"
                                :class="{ 'bg-graydark dark:bg-meta-4': (selected === 'Productos') }">
                                <x-svg_user />
                                Productos
                                <x-svg_desplegable />
                            </a>

                            <!-- Dropdown Menu Start -->
                            <div class="translate transform overflow-hidden"
                                :class="(selected === 'Productos') ? 'block' : 'hidden'">
                                <ul class="mb-5.5 mt-4 flex flex-col gap-2.5 pl-6">
                                    @can('edit producto')
                                        <li>
                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                href="{{ route('producto.index') }}"
                                                :class="{ 'text-white': '{{ request()->routeIs('producto.index') }}' }">
                                                Lista de Productos
                                            </a>
                                        </li>
                                    @endcan
                                    <li>
                                        <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                            href="{{ route('producto.precios-mayorista') }}"
                                            :class="{ 'text-white': '{{ request()->routeIs('producto.precios-mayorista') }}' }">
                                            Precios Mayorista
                                        </a>
                                    </li>

                                    <li>
                                        <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                            href="{{ route('producto.precios-bodega') }}"
                                            :class="{ 'text-white': '{{ request()->routeIs('producto.precios-bodega') }}' }">
                                            Precios Bodega
                                        </a>
                                    </li>
                                    @can('stock producto')
                                        <li>
                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                href="{{ route('producto.stock') }}"
                                                :class="{ 'text-white': '{{ request()->routeIs('producto.stock') }}' }">
                                                Stock de Productos
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                            <!-- Dropdown Menu End -->
                        </li>
                    @endcan

                    <!-- Pedido -->
                    @can('view pedido')
                        <li>
                            <a href="{{ route('pedido.index') }}"
                                class="group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4" :class="{ 'bg-graydark dark:bg-meta-4': '{{ request()->routeIs('pedido.index') }}' }">
                                <x-svg_tecla />
                                Pedido
                            </a>
                        </li>
                    @endcan

                    <!-- Categoría -->
                    @can('view categoria')
                        <li>
                            <a href="{{ route('categoria.index') }}" @click="selected = 'Categoria'"
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
                            <a href="{{ route('proveedor.index') }}" @click="selected = 'Proveedor'"
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
                        <li>
                            <a class="group relative flex items-center gap-2.5 rounded-sm px-4 py-2 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4"
                                href="#" @click.prevent="selected = (selected === 'Movimientos' ? '':'Movimientos')"
                                :class="{ 'bg-graydark dark:bg-meta-4': (selected === 'Movimientos') }">

                                <x-svg_grid_add />

                                Movimientos
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 fill-current"
                                    :class="{ 'rotate-180': (selected === 'Movimientos') }" width="20"
                                    height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4.41107 6.9107C4.73651 6.58527 5.26414 6.58527 5.58958 6.9107L10.0003 11.3214L14.4111 6.91071C14.7365 6.58527 15.2641 6.58527 15.5896 6.91071C15.915 7.23614 15.915 7.76378 15.5896 8.08922L10.5896 13.0892C10.2641 13.4147 9.73651 13.4147 9.41107 13.0892L4.41107 8.08922C4.08563 7.76378 4.08563 7.23614 4.41107 6.9107Z"
                                        fill="" />
                                </svg>
                            </a>

                            <!-- Dropdown Menu Start -->
                            <div class="translate transform overflow-hidden"
                                :class="(selected === 'Movimientos') ? 'block' : 'hidden'">
                                <ul class="mb-5.5 mt-4 flex flex-col gap-2.5 pl-6">
                                    @can('create movimiento')
                                        <li>
                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                href="{{ route('movimiento.create') }}"
                                                :class="{ 'text-white': '{{ request()->routeIs('movimiento.create') }}' }">
                                                Ingresar Movimiento
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view movimiento')
                                        <li>
                                            <a class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                                                href="{{ route('movimiento.view') }}"
                                                :class="{ 'text-white': '{{ request()->routeIs('movimiento.view') }}' }">
                                                Ver Movimientos
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                            <!-- Dropdown Menu End -->
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
                            <a href="{{ route('user.lista') }}" @click="selected = 'User'"
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
                            <a href="{{ route('user-roles.index') }}" @click="selected = 'UserRoles'"
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
