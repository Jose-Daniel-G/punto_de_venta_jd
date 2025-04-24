@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Sistema de reservas</h1>
@stop

@section('content')

    {{-- @can('crear-categoria') --}}
        <div class="mb-4">
            <a href="{{ route('categorias.create') }}">
                <button type="button" class="btn btn-primary">Añadir nuevo registro</button>
            </a>
        </div>
    {{-- @endcan --}}

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla categorías
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->caracteristica->nombre }}</td>
                            <td>{{ $categoria->caracteristica->descripcion }}</td>
                            <td>
                                @if ($categoria->caracteristica->estado == 1)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Eliminado</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">

                                    {{-- Dropdown de opciones --}}
                                    <div class="dropdown">
                                        <button title="Opciones" class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @can('editar-categoria')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('categorias.edit', ['categoria' => $categoria]) }}">
                                                        Editar
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>

                                    {{-- Botón eliminar/restaurar --}}
                                    @can('eliminar-categoria')
                                        <button 
                                            title="{{ $categoria->caracteristica->estado == 1 ? 'Eliminar' : 'Restaurar' }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmModal-{{ $categoria->id }}" 
                                            class="btn btn-sm btn-outline-dark">
                                            @if ($categoria->caracteristica->estado == 1)
                                                <i class="far fa-trash-alt"></i>
                                            @else
                                                <i class="fas fa-rotate"></i>
                                            @endif
                                        </button>
                                    @endcan

                                </div>
                            </td>
                        </tr>

                        {{-- Modal Confirmación --}}
                        <div class="modal fade" id="confirmModal-{{ $categoria->id }}" tabindex="-1" aria-labelledby="confirmModalLabel-{{ $categoria->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmModalLabel-{{ $categoria->id }}">Mensaje de confirmación</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        {{ $categoria->caracteristica->estado == 1 ? '¿Seguro que quieres eliminar la categoría?' : '¿Seguro que quieres restaurar la categoría?' }}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        <form action="{{ route('categorias.destroy', ['categoria' => $categoria->id]) }}" method="POST">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Confirmar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop

@section('css')
    {{-- Puedes agregar tus estilos personalizados aquí --}}
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
@stop
