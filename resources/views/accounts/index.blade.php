@extends('layouts.app')

@section('title', 'Contas - Finanças Casal')
@section('page_title', 'Minhas Contas')

@section('actions')
    <a href="{{ route('accounts.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-plus-lg"></i> Nova Conta
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Saldo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                    <tr>
                        <td>
                            {{ $account->name }}
                            @if($account->is_benefit)
                                <span class="badge bg-info text-dark" style="font-size: 0.6rem;">BENEFÍCIO</span>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ ucfirst($account->type) }}</span></td>
                        <td class="{{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                            R$ {{ number_format($account->balance, 2, ',', '.') }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('accounts.show', $account) }}" class="btn btn-sm btn-outline-primary" title="Ver Extrato">
                                <i class="bi bi-list-columns-reverse"></i> Extrato
                            </a>
                            <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhuma conta cadastrada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
