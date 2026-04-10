@extends('layouts.app')

@section('title', 'Nova Categoria - Finanças Casal')
@section('page_title', 'Nova Categoria')

@section('actions')
    <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-6 mx-auto">
    <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nome da Categoria</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Uso</label>
                <select name="type" class="form-select" required>
                    <option value="both">Ambos (Receita e Despesa)</option>
                    <option value="expense">Apenas Despesa</option>
                    <option value="income">Apenas Receita</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Cor (Opcional - Usado em gráficos)</label>
                <input type="color" name="color" class="form-control form-control-color" value="#0d6efd" title="Escolha uma cor">
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar Categoria</button>
        </form>
    </div>
</div>
@endsection
