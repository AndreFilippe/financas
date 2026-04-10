@extends('layouts.app')

@section('title', 'Nova Lista de Compras')
@section('page_title', 'Criar Novo Planejamento de Compra')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('shopping-lists.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome da Lista</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Compras do Mês" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Local / Mercado (Opcional)</label>
                        <input type="text" name="location" class="form-control" placeholder="Ex: Carrefour, Assaí...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Data Planejada</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">Criar e Adicionar Itens</button>
                        <a href="{{ route('shopping-lists.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
