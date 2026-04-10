@extends('layouts.app')

@section('title', 'Cartões de Crédito - Finanças Casal')
@section('page_title', 'Meus Cartões')

@section('actions')
    <a href="{{ route('credit-cards.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-plus-lg"></i> Novo Cartão
    </a>
@endsection

@section('content')
<div class="row">
    @forelse($cards as $card)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ $card->name }}</h5>
                    <p class="text-muted mb-1">Limite: R$ {{ number_format($card->limit, 2, ',', '.') }}</p>
                    <p class="mb-3">
                        <small>Vencimento dia {{ $card->due_day }} | Fechamento dia {{ $card->closing_day }}</small>
                    </p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('credit-cards.show', $card) }}" class="btn btn-sm btn-outline-primary flex-fill">Ver Faturas</a>
                        <a href="{{ route('credit-cards.edit', $card) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted">
            Nenhum cartão cadastrado.
        </div>
    @endforelse
</div>
@endsection
