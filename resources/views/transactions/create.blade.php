@extends('layouts.app')

@section('title', 'Nova Transação - Finanças Casal')
@section('page_title', 'Nova Transação')

@section('actions')
    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-8 mx-auto">
    <div class="card-body">
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Lançamento</label>
                    <select name="type" class="form-select" required>
                        <option value="expense">Despesa</option>
                        <option value="income">Receita</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <input type="text" name="description" class="form-control" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Conta</label>
                    <select name="account_id" class="form-select" required>
                        <option value="">Selecione uma conta...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Categoria (Opcional)</label>
                    <select name="category_id" class="form-select">
                        <option value="">Selecione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Data</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" id="statusSelect" class="form-select" required>
                        <option value="paid">Confirmado / Pago</option>
                        <option value="pending">Pendente (Conta a Pagar/Receber)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurring">
                        <label class="form-check-label" for="isRecurring">
                            Recorrente
                        </label>
                    </div>
                </div>
                <div class="col-md-2" id="repeatUntilContainer" style="display: none;">
                    <label class="form-label">Repetir até:</label>
                    <input type="date" name="repeat_until" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Salvar Transação</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRecurring = document.getElementById('isRecurring');
    const container = document.getElementById('repeatUntilContainer');

    isRecurring.addEventListener('change', function() {
        container.style.display = this.checked ? 'block' : 'none';
    });
});
</script>
@endpush
@endsection
