@extends('layouts.app')

@section('title', 'Editar Usuário - Finanças Casal')
@section('page_title', 'Editar Usuário')

@section('actions')
    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card col-md-6 mx-auto shadow-sm border-0">
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

        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label fw-bold">Nome Completo</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
            </div>

            <hr>
            <h6 class="fw-bold mb-3 mt-2 text-muted"><i class="bi bi-lock"></i> Alterar Senha <small class="fw-normal">(Deixe em branco para não alterar)</small></h6>

            <div class="mb-3">
                <label class="form-label fw-bold">Nova Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" class="form-control">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Confirme a Nova Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key-fill"></i></span>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm mb-3">Salvar Alterações</button>
        </form>

        @if(Auth::id() !== $user->id)
        <form action="{{ route('users.destroy', $user) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-outline-danger w-100 confirm-action" data-confirm-title="Excluir Usuário?" data-confirm-text="Remover o acesso deste usuário permanentemente?">Excluir Usuário</button>
        </form>
        @else
        <div class="alert alert-info py-2 small mb-0 text-center">
            Você está editando seu próprio acesso.
        </div>
        @endif
    </div>
</div>
@endsection
