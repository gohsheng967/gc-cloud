{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Dashboard  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            Hi, and Welcome!
        </div>
    </div>

    <div class="row">
        @if(Auth::user()->hasRole('administrator'))
        <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $userCount }}</h3>

                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user-plus"></i>
                </div>
                <a href="{{ route('users') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $reportToday }}</h3>

                    <p>Total Scan Today</p>
                </div>
                <div class="icon">
                    <i class="fa fa-database"></i>
                </div>
                <a href="{{ route('reports') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admin'))
        <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-gray">
                <div class="inner">
                    <h3>{{ $qrCodeCount }}</h3>

                    <p>Total Qr Code</p>
                </div>
                <div class="icon">
                    <i class="fa fa-qrcode"></i>
                </div>
                <a href="{{ route('product') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
