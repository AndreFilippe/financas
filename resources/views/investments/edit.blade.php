@extends('layouts.app')

@section('title', 'Editar Investimento - Finanças Casal')
@section('page_title', 'Editar Investimento')

@section('actions')
    <a href="{{ route('investments.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-8 mx-auto">
    <div class="card-body">
        <form action="{{ route('investments.update', $investment) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nome da Aplicação</label>
                    <input type="text" name="name" value="{{ $investment->name }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo</label>
                    <input type="text" name="type" value="{{ $investment->type }}" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Data de Início</label>
                    <input type="date" name="start_date" value="{{ $investment->start_date->format('Y-m-d') }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor Investido (R$)</label>
                    <input type="number" step="0.01" name="invested_amount" value="{{ $investment->invested_amount }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Saldo Atual (R$)</label>
                    <input type="number" step="0.01" name="current_balance" value="{{ $investment->current_balance }}" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Rentabilidade Estimada (% ao ano) - Opcional</label>
                <input type="number" step="0.01" name="estimated_profitability" value="{{ $investment->estimated_profitability }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Atualizar Investimento</button>
        </form>

        <form action="{{ route('investments.destroy', $investment) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger w-100 confirm-action" data-confirm-title="Excluir Investimento?" data-confirm-text="Esta ação removerá o registro do investimento permanentemente.">Excluir</button>
        </form>
    </div>
</div>
@endsection
