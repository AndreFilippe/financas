@extends('layouts.app')

@section('title', 'Novo Usuário - Finanças Casal')
@section('page_title', 'Novo Usuário')

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

        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">Nome Completo</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <small class="text-muted">No mínimo 6 caracteres.</small>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Confirme a Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key-fill"></i></span>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Cadastrar Usuário</button>
        </form>
    </div>
</div>
@endsection
