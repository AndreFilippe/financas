@extends('layouts.app')

@section('title', 'Listas de Compras - Finanças Casal')
@section('page_title', 'Listas de Compras de Mercado')

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('shopping-lists.dashboard') }}" class="btn btn-sm btn-outline-primary shadow-sm">
            <i class="bi bi-graph-up"></i> Dashboard
        </a>
        <a href="{{ route('shopping-lists.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="bi bi-plus-lg"></i> Nova Lista
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    @forelse($lists as $list)
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3">
                <span class="badge {{ $list->status == 'open' ? 'bg-primary' : 'bg-success' }}">
                    {{ $list->status == 'open' ? 'Aberta' : 'Finalizada' }}
                </span>
                <small class="text-muted">{{ $list->date->format('d/m/Y') }}</small>
            </div>
            <div class="card-body">
                <h5 class="card-title fw-bold mb-1">{{ $list->name }}</h5>
                @if($list->location)
                    <div class="text-primary small mb-2">
                        <i class="bi bi-geo-alt"></i> {{ $list->location }}
                    </div>
                @endif
                <p class="text-muted small mb-3">
                    <i class="bi bi-cart"></i> {{ $list->items_count }} itens
                </p>
                
                @php
                    $accountsUsed = $list->transactions->map(fn($t) => $t->account->name ?? 'N/A')->unique();
                @endphp
                @if($accountsUsed->count() > 0)
                    <div class="mb-3 d-flex flex-wrap gap-1">
                        @foreach($accountsUsed as $accName)
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.65rem;">
                                <i class="bi bi-bank"></i> {{ $accName }}
                            </span>
                        @endforeach
                    </div>
                @endif

                @if($list->total_amount > 0)
                    <div class="h4 fw-bold text-dark">R$ {{ number_format($list->total_amount, 2, ',', '.') }}</div>
                @endif
            </div>
            <div class="card-footer bg-white border-0 pb-3 d-flex gap-2">
                <a href="{{ route('shopping-lists.show', $list) }}" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-eye"></i> Ver
                </a>
                <a href="{{ route('shopping-lists.copy', $list) }}" class="btn btn-sm btn-outline-info flex-fill" title="Duplicar para próxima compra">
                    <i class="bi bi-copy"></i>
                </a>
                <form action="{{ route('shopping-lists.destroy', $list) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger confirm-action" 
                            data-confirm-title="Excluir Lista?" 
                            data-confirm-text="Esta ação removerá a lista e todos os seus itens permanentemente.">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="text-muted mb-3">
            <i class="bi bi-basket fs-1 text-light"></i>
        </div>
        <h4>Nenhuma lista de compras criada.</h4>
        <p class="text-muted">Comece planejando suas compras de mercado agora mesmo!</p>
        <a href="{{ route('shopping-lists.create') }}" class="btn btn-primary">Criar Primeira Lista</a>
    </div>
    @endforelse
</div>
@endsection
