@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- Barra de Título y Búsqueda --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Inventario y Stock</h2>
        
        {{-- Barra de Búsqueda Grande con Lupa --}}
        <div class="input-group input-group-lg" style="width: 350px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            {{-- El ID 'inventory-search' ahora es 'product-search' para ser consistente con el script --}}
            <input type="search" id="product-search" class="form-control" placeholder="Buscar producto">
        </div>
    </div>

    {{-- Alerta informativa --}}
    <div class="alert alert-warning">
        <i class="fas fa-info-circle me-2"></i> Esta vista muestra el stock actual y permite ajustar los límites (mínimo/máximo).
    </div>

    {{-- ================================================== --}}
    {{-- CAMBIO: Barra de Filtros de Categoría añadida --}}
    {{-- ================================================== --}}
    <div class="d-flex mb-3 overflow-auto pb-2 border-bottom">
        <button class="btn btn-sm btn-outline-dark me-2 active category-filter" data-category-id="all">Todas</button>
        {{-- Esta variable $categorias ahora viene del InventarioController --}}
        @if(isset($categorias))
            @foreach ($categorias as $cat)
                <button class="btn btn-sm btn-outline-secondary me-2 category-filter" data-category-id="{{ $cat->id }}">{{ $cat->nombre }}</button>
            @endforeach
        @endif
    </div>

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 100px;">Imagen</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-center">Stock Actual</th>
                    <th class="text-center">Mínimo</th>
                    <th class="text-center">Máximo</th>
                    <th class="text-center" style="width: 150px;">Estado</th>
                    <th style="width: 150px;">Ajustar</th>
                </tr>
            </thead>
            {{-- CAMBIO: Añadido id="product-table-body" --}}
            <tbody id="product-table-body">
                @forelse ($productos as $producto)
                    @php
                        $inventario = $producto->inventario;
                        $stock = $inventario->stock ?? 0;
                        $minimo = $inventario->cantidad_minima ?? 0;
                        $alerta_clase = '';
                        
                        if ($stock <= 0) {
                            $alerta_clase = 'bg-dark text-white'; // Agotado
                        } elseif ($stock < $minimo) {
                            $alerta_clase = 'bg-danger text-white'; // Stock bajo
                        } elseif ($stock < $minimo * 1.5) { 
                            $alerta_clase = 'bg-warning'; // Advertencia
                        } else {
                            $alerta_clase = 'bg-light text-dark border'; // Normal
                        }
                    @endphp
                    {{-- CAMBIO: Añadido class="product-row" y data-category-id --}}
                    <tr class="product-row" data-category-id="{{ $producto->categoria_id }}">
                        <td>{{ $producto->id }}</td>
                        <td>
                            <img src="{{ $producto->imagen ? asset('storage/' . $producto->imagen) : 'https://placehold.co/100x100/EBF5FB/333333?text=N/A' }}" 
                                 alt="{{ $producto->nombre }}" 
                                 class="img-fluid rounded"
                                 style="width: 60px; height: 60px; object-fit: cover;">
                        </td>
                        {{-- CAMBIO: Añadida class="product-name" para la búsqueda --}}
                        <td class="product-name">{{ $producto->nombre }}</td>
                        <td class="product-category-name">{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $alerta_clase }} fs-6">{{ $stock }}</span>
                        </td>
                        <td class="text-center">{{ $minimo }}</td>
                        <td class="text-center">{{ $inventario->cantidad_maxima ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if ($stock <= 0)
                                <span class="badge bg-dark">AGOTADO</span>
                            @elseif ($stock < $minimo)
                                <span class="badge bg-danger">STOCK BAJO</span>
                            @else
                                <span class="badge bg-success">NORMAL</span>
                            @endif
                        </td>
                        <td>
                            @if (Auth::user()->hasPermissionTo('inventario', 'editar'))
                                <a href="{{ route('inventario.edit', $producto->id) }}" class="btn btn-sm btn-warning me-1" title="Ajustar Stock/Límites">
                                    <i class="fas fa-sliders-h me-1"></i> Ajustar
                                </a>
                            @else
                                <span class="text-muted">Sin permiso</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    {{-- CAMBIO: Añadida class="empty-row" --}}
                    <tr class="empty-row">
                        <td colspan="9" class="text-center text-muted p-4">
                            No hay productos registrados en el inventario.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


{{-- ================================================== --}}
{{-- CAMBIO: Script de filtrado combinado (reemplaza el anterior) --}}
{{-- ================================================== --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        let currentCategoryId = 'all'; 
        // CAMBIO: ID del buscador actualizado a 'product-search'
        const searchInput = document.getElementById('product-search'); 
        const categoryFilters = document.querySelectorAll('.category-filter');
        const tableBody = document.getElementById('product-table-body');
        const emptyRow = tableBody ? tableBody.querySelector('tr.empty-row') : null; 

        function filterProducts() { 
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : ''; 
            const productRows = tableBody ? tableBody.querySelectorAll('tr.product-row') : [];
            
            let visibleRows = 0;

            productRows.forEach(row => {
                const productNameElement = row.querySelector('.product-name');
                const productName = productNameElement ? productNameElement.textContent.toLowerCase() : '';
                const itemCategoryId = row.dataset.categoryId;
                
                const categoryMatch = (currentCategoryId === 'all' || itemCategoryId === currentCategoryId);
                const searchMatch = (searchTerm === '' || productName.includes(searchTerm));
                
                if (categoryMatch && searchMatch) {
                    row.style.display = ''; 
                    visibleRows++;
                } else {
                    row.style.display = 'none'; 
                }
            });

            // Lógica para mostrar/ocultar el mensaje de "No hay productos"
            if (emptyRow) {
                if (visibleRows === 0 && productRows.length > 0) {
                    emptyRow.style.display = '';
                    emptyRow.querySelector('td').textContent = 'No se encontraron productos que coincidan con el filtro.';
                } else if (productRows.length === 0) {
                    emptyRow.style.display = '';
                    emptyRow.querySelector('td').textContent = 'No hay productos registrados.';
                } else {
                    emptyRow.style.display = 'none';
                }
            }
        }

        // Listener para los botones de categoría
        categoryFilters.forEach(button => { 
            button.addEventListener('click', function() {
                categoryFilters.forEach(btn => {
                    btn.classList.remove('active', 'btn-outline-dark'); 
                    btn.classList.add('btn-outline-secondary');
                });
                this.classList.add('active', 'btn-outline-dark');
                this.classList.remove('btn-outline-secondary');
                
                currentCategoryId = this.dataset.categoryId;
                filterProducts(); 
            });
        });
        
        // Listener para la barra de búsqueda
        if (searchInput) { 
            searchInput.addEventListener('input', filterProducts); 
        }

        // (Aquí puedes añadir el script del modal de eliminación si también lo necesitas)
    });
</script>
@endpush