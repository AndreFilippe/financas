@extends('layouts.app')

@section('title', 'Editar Transação - Finanças Casal')
@section('page_title', 'Editar Transação')

@section('actions')
    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-8 mx-auto">
    <div class="card-body">
        <form action="{{ route('transactions.update', $transaction) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Lançamento</label>
                    <select name="type" class="form-select" required>
                        <option value="expense" {{ $transaction->type == 'expense' ? 'selected' : '' }}>Despesa</option>
                        <option value="income" {{ $transaction->type == 'income' ? 'selected' : '' }}>Receita</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ $transaction->amount }}" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <input type="text" name="description" class="form-control" value="{{ $transaction->description }}" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Conta</label>
                    <select name="account_id" class="form-select" required>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $transaction->account_id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Categoria</label>
                    <select name="category_id" class="form-select">
                        <option value="">Selecione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $transaction->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Data</label>
                    <input type="date" name="date" class="form-control" value="{{ $transaction->date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="paid" {{ $transaction->status == 'paid' ? 'selected' : '' }}>Confirmado / Pago</option>
                        <option value="pending" {{ $transaction->status == 'pending' ? 'selected' : '' }}>Pendente (Conta a Pagar/Receber)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurring" {{ $transaction->is_recurring ? 'checked' : '' }}>
                        <label class="form-check-label" for="isRecurring">
                            Repetir todo mês (Recorrente)
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Atualizar Transação</button>
        </form>

        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger w-100 confirm-action" data-confirm-title="Excluir Transação?" data-confirm-text="Esta ação removerá o lançamento do extrato permanentemente.">Excluir Transação</button>
        </form>
    </div>
</div>
@endsection
