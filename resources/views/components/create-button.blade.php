<div class="flex justify-between mb-4">
    <button wire:click="openModal" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
        Crear Nuevo Pedido
    </button>
</div>

@if($showModalForm)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="addPedido">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <label for="ruta_id" class="block text-sm font-medium text-gray-700">Ruta</label>
                            <select wire:model.defer="ruta_id" id="ruta_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione una ruta</option>
                                @foreach($rutas as $ruta)
                                    <option value="{{ $ruta->id }}">{{ $ruta->name }}</option>
                                @endforeach
                            </select>
                            @error('ruta_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="f_tipo_comprobante_id" class="block text-sm font-medium text-gray-700">Tipo Comprobante</label>
                            <select wire:model.defer="f_tipo_comprobante_id" id="f_tipo_comprobante_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione un tipo de comprobante</option>
                                @foreach($tiposComprobante as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->name }}</option>
                                @endforeach
                            </select>
                            @error('f_tipo_comprobante_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="vendedor_id" class="block text-sm font-medium text-gray-700">Vendedor</label>
                            <select wire:model.defer="vendedor_id" id="vendedor_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione un vendedor</option>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                                @endforeach
                            </select>
                            @error('vendedor_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="conductor_id" class="block text-sm font-medium text-gray-700">Conductor</label>
                            <select wire:model.defer="conductor_id" id="conductor_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione un conductor</option>
                                @foreach($conductores as $conductor)
                                    <option value="{{ $conductor->id }}">{{ $conductor->name }}</option>
                                @endforeach
                            </select>
                            @error('conductor_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700">Cliente</label>
                            <select wire:model.defer="cliente_id" id="cliente_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione un cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('cliente_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="fecha_emision" class="block text-sm font-medium text-gray-700">Fecha Emisión</label>
                            <input type="date" wire:model.defer="fecha_emision" id="fecha_emision" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('fecha_emision') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="importe_total" class="block text-sm font-medium text-gray-700">Importe Total</label>
                            <input type="number" step="0.01" wire:model.defer="importe_total" id="importe_total" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('importe_total') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="nro_doc_liquidacion" class="block text-sm font-medium text-gray-700">Nro. Doc. Liquidación</label>
                            <input type="text" wire:model.defer="nro_doc_liquidacion" id="nro_doc_liquidacion" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('nro_doc_liquidacion') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="lista_precio" class="block text-sm font-medium text-gray-700">Lista Precio</label>
                            <select wire:model.defer="lista_precio" id="lista_precio" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione una lista de precios</option>
                                @foreach($listaPrecios as $lista)
                                    <option value="{{ $lista->id }}">{{ $lista->name }}</option>
                                @endforeach
                            </select>
                            @error('lista_precio') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="empresa_id" class="block text-sm font-medium text-gray-700">Empresa</label>
                            <select wire:model.defer="empresa_id" id="empresa_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione una empresa</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('empresa_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif