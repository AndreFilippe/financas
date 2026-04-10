@extends('layouts.app')

@section('title', 'Novo Cartão - Finanças Casal')
@section('page_title', 'Novo Cartão de Crédito')

@section('actions')
    <a href="{{ route('credit-cards.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-6 mx-auto">
    <div class="card-body">
        <form action="{{ route('credit-cards.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nome do Cartão (Ex: Nubank, Itaú)</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Limite R$</label>
                <input type="number" step="0.01" name="limit" class="form-control" required>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Dia de Fechamento</label>
                    <input type="number" name="closing_day" class="form-control" min="1" max="31" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Dia de Vencimento</label>
                    <input type="number" name="due_day" class="form-control" min="1" max="31" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar Cartão</button>
        </form>
    </div>
</div>
@endsection
