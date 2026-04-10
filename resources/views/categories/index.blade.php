@extends('layouts.app')

@section('title', 'Categorias - Finanças Casal')
@section('page_title', 'Minhas Categorias')

@section('actions')
    <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-plus-lg"></i> Nova Categoria
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Cor</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>
                            <div style="width: 20px; height: 20px; border-radius: 50%; background-color: {{ $category->color ?? '#ccc' }};"></div>
                        </td>
                        <td>{{ $category->name }}</td>
                        <td>
                            @if($category->type == 'income') Receita
                            @elseif($category->type == 'expense') Despesa
                            @else Ambos @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhuma categoria cadastrada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
