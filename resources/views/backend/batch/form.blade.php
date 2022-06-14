@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Batch ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Batch Setting</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $data->button_text }} Batch</h3>
            <a href="{{ url()->previous() }}" class ="btn btn-sm btn-warning float-right">Back to Last Page</a>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off')) }}
        {{ Form::hidden('id', $data->id, array('id' => 'id')) }}

        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-3 col-content">
                    {{ Form::text('sku_id', $data->sku_id, array('class' => 'form-control', 'hidden')) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">SKU Code</strong>
                </div>
                <div class="col-sm-3 col-content">
                    {{ Form::text('sku_code', $data->sku_code, array('class' => 'form-control', 'readonly')) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Batch No</strong>
                </div>
                <div class="col-sm-3 col-content">
                    {{ Form::text('batch_no', $data->batch_no, array('class' => 'form-control', 'required')) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Delivery Date</strong>
                </div>
                <div class="col-sm-3 col-content">
                    {{ Form::date('delivery_date', $data->delivery_date, array('class' => 'form-control', 'required')) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Expired Date</strong>
                </div>
                <div class="col-sm-3 col-content">
                    {{ Form::date('expired_date', $data->expired_date, array('class' => 'form-control', 'required')) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Remark</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('remark', $data->remark, array('class' => 'form-control')) }}
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">{{ $data->button_text }}</button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
    @if($html)
    <!-- /.card -->
    <div class="card-body">
        <div class="table-responsive">
            {!! $html->table(['class' => 'table table-hover']) !!}
        </div>
    </div>
    @endif
    <!-- /.row -->
    <!-- /.content -->
@stop

@section('css')
    <link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet">
@stop


@section('js')
    <script>var typePage = "{{ $data->page_type }}";</script>
    <!--Data tables-->
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/jszip/jszip.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/pdfmake/pdfmake.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/pdfmake/vfs_fonts.js') }}"></script>
    {{--Button--}}
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/dataTables.buttons.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.colVis.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.html5.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.print.js') }}"></script>
    @if($html)
     {!! $html->scripts() !!}
     @endif
    <script src="{{ asset('js/backend/histories/form.js'). '?v=' . rand(99999,999999) }}"></script>

@stop
