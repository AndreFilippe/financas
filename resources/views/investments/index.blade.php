@extends('layouts.app')

@section('title', 'Investimentos - Finanças Casal')
@section('page_title', 'Meus Investimentos')

@section('actions')
    <a href="{{ route('investments.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-plus-lg"></i> Novo Investimento
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nome / Tipo</th>
                        <th>Data Inicial</th>
                        <th>Valor Investido</th>
                        <th>Saldo Atual</th>
                        <th>Retorno</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investments as $inv)
                        @php
                            $profit = $inv->current_balance - $inv->invested_amount;
                            $profitPercent = $inv->invested_amount > 0 ? ($profit / $inv->invested_amount) * 100 : 0;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $inv->name }}</strong><br>
                                <small class="text-muted">{{ $inv->type }}</small>
                            </td>
                            <td>{{ $inv->start_date->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($inv->invested_amount, 2, ',', '.') }}</td>
                            <td class="fw-bold">R$ {{ number_format($inv->current_balance, 2, ',', '.') }}</td>
                            <td class="{{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $profit >= 0 ? '+' : '' }}R$ {{ number_format($profit, 2, ',', '.') }} 
                                ({{ number_format($profitPercent, 2, ',', '.') }}%)
                            </td>
                            <td class="text-end">
                                <a href="{{ route('investments.edit', $inv) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Nenhum investimento cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
