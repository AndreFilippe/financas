@extends('layouts.app')

@section('title', 'Editar Conta - Finanças Casal')
@section('page_title', 'Editar Conta')

@section('actions')
    <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-6 mx-auto">
    <div class="card-body">
        <form action="{{ route('accounts.update', $account) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nome da Conta</label>
                <input type="text" name="name" class="form-control" value="{{ $account->name }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo</label>
                <select name="type" class="form-select" required>
                    <option value="checking" {{ $account->type == 'checking' ? 'selected' : '' }}>Conta Corrente</option>
                    <option value="savings" {{ $account->type == 'savings' ? 'selected' : '' }}>Poupança</option>
                    <option value="benefit" {{ $account->type == 'benefit' || $account->is_benefit ? 'selected' : '' }}>Benefício (VA/VR)</option>
                    <option value="investment" {{ $account->type == 'investment' ? 'selected' : '' }}>Investimento</option>
                    <option value="other" {{ $account->type == 'other' ? 'selected' : '' }}>Outros</option>
                </select>
            </div>
            <div class="mb-3 form-check form-switch px-5">
                <input type="checkbox" name="is_benefit" class="form-check-input" id="is_benefit" value="1" {{ $account->is_benefit ? 'checked' : '' }}>
                <label class="form-check-label fw-bold" for="is_benefit">É uma conta de Benefício? (VA/VR)</label>
                <small class="text-muted d-block mt-1">O saldo desta conta não será somado ao dinheiro do casal no dashboard.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Saldo Atual</label>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="number" step="0.01" name="balance" class="form-control" value="{{ $account->balance }}" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Atualizar Conta</button>
        </form>
        
        <form action="{{ route('accounts.destroy', $account) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger w-100 confirm-action" data-confirm-title="Excluir Conta?" data-confirm-text="Esta ação removerá a conta e todo o seu histórico permanentemente.">Excluir Conta</button>
        </form>
    </div>
</div>
@endsection
