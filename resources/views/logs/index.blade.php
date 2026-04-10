@extends('layouts.app')

@section('title', 'Trilha de Auditoria - Finanças Casal')
@section('page_title', 'Trilha de Auditoria')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock text-primary me-2"></i> Registro de Atividades</h5>
        <span class="ms-auto badge bg-secondary">Registros seguros WORM (Write-Once, Read-Many)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Data/Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Entidade</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-4 text-nowrap"><small class="text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</small></td>
                        <td>
                            <span class="fw-bold">{{ $log->causer ? $log->causer->name : 'Sistema/Desconhecido' }}</span>
                        </td>
                        <td>
                            @if($log->event == 'created')
                                <span class="badge bg-success">Criação</span>
                            @elseif($log->event == 'updated')
                                <span class="badge bg-warning text-dark">Edição</span>
                            @elseif($log->event == 'deleted')
                                <span class="badge bg-danger">Exclusão</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($log->event) }}</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</small>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                <i class="bi bi-eye"></i> Ver Alterações
                            </button>

                            <!-- Modal de Detalhes -->
                            <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detalhes da Ação: {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @if(isset($log->properties['old']) && isset($log->properties['attributes']))
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold text-danger">Antes:</h6>
                                                        <pre class="bg-light p-3 rounded" style="font-size: 0.8rem;">{!! json_encode($log->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold text-success">Depois:</h6>
                                                        <pre class="bg-light p-3 rounded" style="font-size: 0.8rem;">{!! json_encode($log->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                                    </div>
                                                </div>
                                            @elseif(isset($log->properties['attributes']))
                                                <h6 class="fw-bold text-success">Atributos:</h6>
                                                <pre class="bg-light p-3 rounded" style="font-size: 0.8rem;">{!! json_encode($log->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                            @elseif(isset($log->properties['old']))
                                                <h6 class="fw-bold text-danger">Excluído:</h6>
                                                <pre class="bg-light p-3 rounded" style="font-size: 0.8rem;">{!! json_encode($log->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                            @else
                                                <p class="text-muted">Nenhum dado capturado.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Nenhum registro de atividade encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
