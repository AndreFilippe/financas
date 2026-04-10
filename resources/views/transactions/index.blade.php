@extends('layouts.app')

@section('title', 'Transações - Finanças Casal')
@section('page_title', 'Lançamentos Financeiros')

@section('actions')
    <a href="{{ route('transactions.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-plus-lg"></i> Novo Lançamento
    </a>
@endsection

@section('content')
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('transactions.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Mês de Referência:</label>
                <div class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                {{ ucfirst(\Carbon\Carbon::create()->month($m)->translatedFormat('F')) }}
                            </option>
                        @endforeach
                    </select>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ request('year', now()->year) }}" style="width: 80px;">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">De:</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Até:</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Descrição:</label>
                <input type="text" name="description" class="form-control form-control-sm" value="{{ request('description') }}" placeholder="O que busca?">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Conta:</label>
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Categoria:</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Conta</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td>{{ $t->date->format('d/m/Y') }}</td>
                        <td>{{ $t->description }}</td>
                        <td>{{ $t->account->name }}</td>
                        <td>{{ $t->category ? $t->category->name : '-' }}</td>
                        <td class="{{ $t->type == 'income' ? 'text-success' : 'text-danger' }} fw-bold">
                            {{ $t->type == 'income' ? '+' : '-' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
                        </td>
                        <td>
                            @if($t->status == 'paid')
                                <span class="badge bg-success">Pago</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendente</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('transactions.edit', $t) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Nenhuma transação encontrada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
