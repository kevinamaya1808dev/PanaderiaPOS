@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- Título --}}
    <h2 class="mb-3">Gestión de Productos</h2>

    {{-- ================================================== --}}
    {{-- CAMBIO: Barra de Búsqueda y Botón Crear --}}
    {{-- ================================================== --}}
    <div class="row mb-3">
        {{-- Barra de Búsqueda (copiada del TPV) --}}
        <div class="col-md-8 mb-2 mb-md-0">
            <div class="input-group shadow-sm">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="search" id="product-search" class="form-control" placeholder="Buscar producto por nombre...">
            </div>
        </div>
        {{-- Botón para CREAR PRODUCTO --}}
        <div class="col-md-4 text-end">
            @if (Auth::user()->hasPermissionTo('productos', 'alta'))
                <a href="{{ route('productos.create') }}" class="btn btn-primary w-100">
                    <i class="fas fa-plus-circle me-1"></i> Crear Nuevo Producto
                </a>
            @endif
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- CAMBIO: Barra de Filtros de Categoría (copiada del TPV) --}}
    {{-- ================================================== --}}
    <div class="d-flex mb-3 overflow-auto pb-2 border-bottom">
        <button class="btn btn-sm btn-outline-dark me-2 active category-filter" data-category-id="all">Todas</button>
        {{-- Esta variable $categorias debe venir del ProductoController --}}
        @if(isset($categorias)) {{-- Comprobación por si el controller aún no se actualiza --}}
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
                    <th>Nombre</th>
                    <th style="width: 100px;">Imagen</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th style="width: 200px;">Acciones</th>
                </tr>
            </thead>
            {{-- CAMBIO: Añadido id="product-table-body" --}}
            <tbody id="product-table-body">
                @forelse ($productos as $producto)
                {{-- CAMBIO: Añadido class="product-row" y data-category-id --}}
                <tr class="product-row" data-category-id="{{ $producto->categoria_id }}">
                    <td>{{ $producto->id }}</td>
                    {{-- CAMBIO: Añadida class="product-name" para la búsqueda --}}
                    <td class="product-name">{{ $producto->nombre }}</td>
                    <td>
                        <img src="{{ $producto->imagen ? asset('storage/' . $producto->imagen) : 'https://placehold.co/100x100/EBF5FB/333333?text=N/A' }}" 
                             alt="{{ $producto->nombre }}" 
                             class="img-fluid rounded"
                             style="width: 60px; height: 60px; object-fit: cover;">
                    </td>
                    {{-- CAMBIO: Añadida class="product-category-name" (para referencia) --}}
                    <td class="product-category-name">{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                    <td>${{ number_format($producto->precio, 2) }}</td>
                    <td>
                        {{ $producto->inventario->stock ?? '0' }}
                    </td>
                    <td>
                        {{-- Editar --}}
                        @if (Auth::user()->hasPermissionTo('productos', 'editar'))
                            <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>
                        @endif

                        {{-- Eliminar (con modal) --}}
                        @if (Auth::user()->hasPermissionTo('productos', 'eliminar'))
                            <button type="button" class="btn btn-sm btn-danger" 
                                    title="Eliminar"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmDeleteModal"
                                    data-item-nombre="{{ $producto->nombre }}"
                                    data-form-action="{{ route('productos.destroy', $producto->id) }}">
                                <i class="fas fa-trash me-1"></i> Eliminar
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                {{-- CAMBIO: Añadida class="empty-row" --}}
                <tr class="empty-row">
                    <td colspan="7" class="text-center text-muted p-4">
                        No hay productos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- Modal de Confirmación de Eliminación (Genérico) --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar: 
                <br>
                <strong id="modalItemNombre" class="fs-5"></strong>?
                <br><br>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Sí, Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


{{-- ================================================== --}}
{{-- CAMBIO: Script de filtrado del TPV, adaptado para la tabla --}}
{{-- ================================================== --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        let currentCategoryId = 'all'; 
        const categoryFilters = document.querySelectorAll('.category-filter');
        const searchInput = document.getElementById('product-search'); 
        const tableBody = document.getElementById('product-table-body');
        const emptyRow = tableBody ? tableBody.querySelector('tr.empty-row') : null; // Fila de "No hay productos"

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
                    row.style.display = ''; // Muestra la fila (CSS default de <tr>)
                    visibleRows++;
                } else {
                    row.style.display = 'none'; // Oculta la fila
                }
            });

            // Lógica para mostrar/ocultar el mensaje de "No hay productos"
            if (emptyRow) {
                if (visibleRows === 0 && productRows.length > 0) {
                    // Si hay filas pero ninguna coincide con el filtro
                    emptyRow.style.display = '';
                    emptyRow.querySelector('td').textContent = 'No se encontraron productos que coincidan con el filtro.';
                } else if (productRows.length === 0) {
                    // Si la tabla está vacía desde el servidor
                    emptyRow.style.display = '';
                    emptyRow.querySelector('td').textContent = 'No hay productos registrados.';
                } else {
                    // Si hay filas visibles
                    emptyRow.style.display = 'none';
                }
            }
        }

        // Listener para los botones de categoría
        categoryFilters.forEach(button => { 
            button.addEventListener('click', function() {
                // Cambia el estilo del botón activo
                categoryFilters.forEach(btn => {
                    btn.classList.remove('active', 'btn-outline-dark'); 
                    btn.classList.add('btn-outline-secondary');
                });
                this.classList.add('active', 'btn-outline-dark');
                this.classList.remove('btn-outline-secondary');
                
                // Actualiza la categoría y filtra
                currentCategoryId = this.dataset.categoryId;
                filterProducts(); 
            });
        });
        
        // Listener para la barra de búsqueda
        if (searchInput) { 
            searchInput.addEventListener('input', filterProducts); 
        }

        // Script del Modal de Eliminación (fusionado)
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        if (confirmDeleteModal) {
            confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var itemNombre = button.getAttribute('data-item-nombre'); 
                var formAction = button.getAttribute('data-form-action');
                var modalBodyNombre = confirmDeleteModal.querySelector('#modalItemNombre');
                var deleteForm = confirmDeleteModal.querySelector('#deleteForm');
                modalBodyNombre.textContent = itemNombre;
                deleteForm.setAttribute('action', formAction);
            });
        }
    });
</script>
@endpush