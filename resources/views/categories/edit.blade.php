@extends('layouts.app')

@section('title', 'Editar Categoria - Finanças Casal')
@section('page_title', 'Editar Categoria')

@section('actions')
    <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-6 mx-auto">
    <div class="card-body">
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nome da Categoria</label>
                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Uso</label>
                <select name="type" class="form-select" required>
                    <option value="both" {{ $category->type == 'both' ? 'selected' : '' }}>Ambos (Receita e Despesa)</option>
                    <option value="expense" {{ $category->type == 'expense' ? 'selected' : '' }}>Apenas Despesa</option>
                    <option value="income" {{ $category->type == 'income' ? 'selected' : '' }}>Apenas Receita</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Cor</label>
                <input type="color" name="color" class="form-control form-control-color" value="{{ $category->color ?? '#000000' }}">
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Atualizar Categoria</button>
        </form>

        <form action="{{ route('categories.destroy', $category) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger w-100 confirm-action" data-confirm-title="Excluir Categoria?" data-confirm-text="Esta ação removerá a categoria permanentemente.">Excluir</button>
        </form>
    </div>
</div>
@endsection
