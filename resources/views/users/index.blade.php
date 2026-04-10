@extends('layouts.app')

@section('title', 'Usuários - Finanças Casal')
@section('page_title', 'Administração de Usuários')

@section('actions')
    <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-person-plus-fill"></i> Novo Usuário
    </a>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nome</th>
                        <th>E-mail</th>
                        <th>Cadastrado em</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="ps-4 fw-bold">
                            {{ $user->name }}
                            @if(Auth::id() === $user->id)
                                <span class="badge bg-success ms-1">Você</span>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
