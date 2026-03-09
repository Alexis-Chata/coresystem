<div class="max-w-6xl mx-auto">

    <h2 class="text-xl font-bold mb-6 text-gray-800">Promociones</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 min-[1224px]:grid-cols-3 gap-6">

        @forelse ($lista_promociones as $promocion)
            <!-- Card Base Start -->
            <div
                class="bg-white rounded-[24px] p-4 shadow-[0_2px_10px_rgba(0,0,0,0.04)] hover:shadow-lg transition-shadow duration-300 border border-gray-100 w-full flex flex-col justify-between">

                <div class="flex gap-4">
                    <div class="w-24 shrink-0 flex items-center justify-center">
                        <img src="https://via.placeholder.com/100x150/ffebd6/ffb067?text=Amarás" alt="{{ $promocion->name }}"
                            class="w-full h-auto object-contain" />
                    </div>

                    <div class="flex flex-col flex-1">
                        <span
                            class="text-[#626e8e] text-[11px] font-bold tracking-wider uppercase">{{ $promocion->id }}</span>
                        <h3 class="text-gray-800 text-[15px] font-medium leading-tight mt-1">{{ $promocion->name }}</h3>

                        <div
                            class="flex items-center gap-1.5 bg-[#f0f2fb] text-[#556187] text-xs font-medium px-2.5 py-1 rounded-full w-max mt-2">
                            <svg class="w-3.5 h-3.5 text-[#556187]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 12h3v8h14v-8h3L12 2zm0 2.8l7 7h-2v6H7v-6H5l7-7z" />
                            </svg>
                            {{ $promocion->marca->name }}
                        </div>


                        <!-- <div class="grid grid-cols-[auto_1fr] gap-x-4 items-center mt-3 text-[14px]">
                        <span class="text-gray-400 line-through">S/ 16.90</span>
                        <span class="text-gray-400 text-xs">Precio regular</span>
                        <span class="text-gray-700 mt-1">S/ 12.30</span>
                        <span></span>
                        <span class="text-gray-900 font-bold mt-1">S/ 11.90</span>
                        <div class="flex gap-1 mt-1">
                            <span class="bg-[#e42528] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-sm">oh!</span>
                            <span class="bg-[#e42528] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-sm">oh! pay</span>
                        </div>
                    </div> -->
                    </div>
                </div>
            </div>
            <!-- Card Base End -->

        @empty
            <div class="col-span-full text-center text-gray-500 py-10">
                No hay promociones disponibles en este momento.
            </div>
        @endforelse

    </div>
</div>
