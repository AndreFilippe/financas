@extends('layouts.app')

@section('title', 'Meu Perfil - Finanças Casal')
@section('page_title', 'Meu Perfil')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow" style="width: 80px; height: 80px; font-size: 2rem;">
                    <i class="bi bi-person"></i>
                </div>
                <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                <span class="badge bg-success">Usuário Ativo</span>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock text-warning me-2"></i> Alterar Minha Senha</h5>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Senha Atual</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key-fill text-muted"></i></span>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                    </div>

                    <hr class="text-muted">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nova Senha</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key text-primary"></i></span>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <small class="text-muted">No mínimo 6 caracteres.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Confirme a Nova Senha</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key text-primary"></i></span>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Atualizar Minha Senha</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
