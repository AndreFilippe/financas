@extends('layouts.app')

@section('title', "Cartão {$creditCard->name}")
@section('page_title', "Faturas do Cartão {$creditCard->name}")

@section('actions')
    <button type="button" class="btn btn-sm btn-outline-success shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#importCsvModal">
        <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV Nubank
    </button>
    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newPurchaseModal">
        <i class="bi bi-plus-lg"></i> Lançar Compra
    </button>
    <a href="{{ route('credit-cards.edit', $creditCard) }}" class="btn btn-sm btn-outline-primary" title="Editar Configurações">
        <i class="bi bi-gear"></i> Editar
    </a>
    <a href="{{ route('credit-cards.index') }}" class="btn btn-sm btn-outline-secondary">
        Voltar
    </a>
@endsection

@section('content')

@foreach($creditCard->invoices as $invoice)
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Fatura: {{ $invoice->reference_month }}</h5>
        <span class="badge {{ $invoice->status == 'open' ? 'bg-primary' : ($invoice->status == 'paid' ? 'bg-success' : 'bg-secondary') }}">
            R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}
        </span>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Parcelinha</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->transactions as $t)
                <tr>
                    <td>{{ $t->date->format('d/m/Y') }}</td>
                    <td>{{ $t->description }}</td>
                    <td>{{ $t->current_installment }}/{{ $t->installments }}</td>
                    <td class="text-end">R$ {{ number_format($t->amount, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">Nenhum lançamento.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach

<!-- Modal Nova Compra -->
<div class="modal fade" id="newPurchaseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nova Compra - {{ $creditCard->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('credit-cards.transactions.store', $creditCard) }}" method="POST">
          @csrf
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Data da Compra</label>
                  <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}">
              </div>
              <div class="mb-3">
                  <label class="form-label">Descrição</label>
                  <input type="text" name="description" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Categoria (Opcional)</label>
                  <select name="category_id" class="form-select">
                      <option value="">Selecione...</option>
                      @foreach($categories as $cat)
                      <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                      @endforeach
                  </select>
              </div>
              <div class="row mb-3">
                  <div class="col-6">
                      <label class="form-label">Valor Total</label>
                      <input type="number" step="0.01" name="amount" class="form-control" required>
                  </div>
                  <div class="col-6">
                      <label class="form-label">Parcelas</label>
                      <input type="number" name="installments" class="form-control" value="1" min="1" required>
                  </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Lançar no Cartão</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Importar CSV -->
<div class="modal fade" id="importCsvModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content text-start">
      <div class="modal-header">
        <h5 class="modal-title">Importar Fatura (CSV)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('credit-cards.import', $creditCard) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
              <p class="text-muted small">Suporta o formato padrão do Nubank: <code>date,title,amount</code>.</p>
              <div class="mb-3">
                  <label class="form-label fw-bold small">Mês da Fatura (Opcional)</label>
                  <input type="month" name="reference_month" class="form-control" value="{{ now()->format('Y-m') }}">
                  <small class="text-muted d-block mt-1">Se não selecionado, o sistema usará o dia de fechamento do cartão.</small>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small">Arquivo .csv (Nubank)</label>
                  <input type="file" name="csv_file" class="form-control" accept=".csv" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Processar Arquivo</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
