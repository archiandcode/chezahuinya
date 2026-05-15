@extends('layouts.adminlte')

@section('title', 'Главная | ' . config('app.name'))
@section('page-title', 'Главная')

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\User::count() }}</h3>
                    <p>Пользователи</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Добро пожаловать</h3>
        </div>
        <div class="card-body">
            Вы вошли как <strong>{{ auth()->user()->email }}</strong>.
        </div>
    </div>
@endsection
