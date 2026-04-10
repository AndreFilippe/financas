@extends('layouts.app')

@section('title', "Editar Cartão: {$creditCard->name}")
@section('page_title', "Editar Cartão: {$creditCard->name}")

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('credit-cards.update', $creditCard) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome do Cartão</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $creditCard->name) }}" required>
                        <small class="text-muted">Ex: Nubank, Visa Infinite, etc.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Limite Total (R$)</label>
                        <input type="number" step="0.01" name="limit" class="form-control" value="{{ old('limit', $creditCard->limit) }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Dia de Fechamento</label>
                            <input type="number" name="closing_day" class="form-control" value="{{ old('closing_day', $creditCard->closing_day) }}" min="1" max="31" required>
                            <small class="text-muted">Dia que a fatura vira.</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Dia de Vencimento</label>
                            <input type="number" name="due_day" class="form-control" value="{{ old('due_day', $creditCard->due_day) }}" min="1" max="31" required>
                            <small class="text-muted">Dia de pagamento.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('credit-cards.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
