@extends('layouts.app')

@section('title', 'Revisar Duplicação')
@section('page_title', 'Confirmar Dados da Nova Lista')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="alert alert-info small mb-4">
                    <i class="bi bi-info-circle"></i> Os itens serão copiados e os preços que você <strong>pagou</strong> na lista original serão usados como <strong>estimados</strong> na nova lista.
                </div>

                <form action="{{ route('shopping-lists.duplicate', $shoppingList) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome da Nova Lista</label>
                        <input type="text" name="name" class="form-control" value="{{ $shoppingList->name }} (Cópia)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Novo Local / Mercado</label>
                        <input type="text" name="location" class="form-control" value="{{ $shoppingList->location }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Data Planejada</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary flex-fill">Criar Nova Lista</button>
                        <a href="{{ route('shopping-lists.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
