@extends('layouts.app')

@section('title', 'Novo Investimento - Finanças Casal')
@section('page_title', 'Novo Investimento')

@section('actions')
    <a href="{{ route('investments.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-8 mx-auto">
    <div class="card-body">
        <form action="{{ route('investments.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nome da Aplicação</label>
                    <input type="text" name="name" class="form-control" placeholder="CDB Banco X" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo</label>
                    <input type="text" name="type" class="form-control" placeholder="Renda Fixa, Ações..." required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Data de Início</label>
                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor Investido (R$)</label>
                    <input type="number" step="0.01" name="invested_amount" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Saldo Atual (R$)</label>
                    <input type="number" step="0.01" name="current_balance" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Rentabilidade Estimada (% ao ano) - Opcional</label>
                <input type="number" step="0.01" name="estimated_profitability" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrar Investimento</button>
        </form>
    </div>
</div>
@endsection
