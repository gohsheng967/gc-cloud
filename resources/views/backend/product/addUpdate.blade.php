@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Product Qr ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Product Qr</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add or Update</h3>
            <div class="spinner-border float-right" role="status" id ='spinner'>
                <span class="sr-only ">Loading...</span>
            </div>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'id')) }}
        {{ Form::hidden('old_batch_id', $data->batch_id) }}
        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">SKU Code</strong>
                </div>
                <div class="col-sm-3 col-content">
                    <select name="sku_id" id="sku_id" class = "form-control">
                        <option value="" selected disabled>-- Select SKU Code --</option>
                        @forelse($skuList as $skuCode)
                            @if($data->sku_id == $skuCode->id)
                                <option value="{{$skuCode->id}}" selected>{{$skuCode->sku_code}}</option>
                            @else
                                <option value="{{$skuCode->id}}" >{{$skuCode->sku_code}}</option>
                            @endif
                        @empty
                        <option > No SKU Code</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Batch No</strong>
                </div>
                <div class="col-sm-6 col-content">
                    <select name="batch_id" id="batch_id" class = "form-control">
                        <option value="" selected disabled>-- Select Batch --</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Serial No</strong>
                </div>
                <div class="col-sm-6 col-content">
                    <select name="serial_id" id="serial_id" class = "form-control">
                        <option value="" selected disabled>-- Select Serial No --</option>
                    </select>
                    {{ Form::text('serial_no', $data->serial_no, array('class' => 'form-control mt-1', 'id' =>'serial_no' )) }}

                </div>
                <!-- <div class="col-sm-3 col-content">
                    {{ Form::text('serial_no', $data->serial_no, array('class' => 'form-control', 'required')) }}
                </div> -->
            </div>
            <div class="formgroup row" id ='add_section'>
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Prefix</strong>
                </div>
                <div class="col-sm-2 col-content">
                    {{ Form::text('prefix_1', $data->prefix_1, array('class' => 'form-control mt-1', 'id' =>'prefix_1',  'placeholder' => 'Prefix')) }}
                </div>
                <div class="col-sm-2 col-content">
                    {{ Form::text('running_no', $data->running_no, array('class' => 'form-control mt-1', 'placeholder' => 'Running No')) }}
                </div> 
                <div class="col-sm-1 col-content">
                    {{ Form::number('quantity', $data->quantity, array('class' => 'form-control mt-1')) }}
                </div>         
            </div>
            <div class="mt-3">
                <h5>Last Three Serial Number<br><span id ='last'></span></h5>
            </div>
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit" class="btn btn-primary">{{ $data->button_text }}</button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <!-- /.card -->
    </div>
    <!-- /.row -->
    <!-- /.content -->
@stop

@section('css')

@stop

@section('js')
    <script>var typePage = "{{ $data->page_type }}";</script>

    <script src="{{ asset('js/backend/histories/form.js'). '?v=' . rand(99999,999999) }}"></script>
    <script>
    $( document ).ready(function() {
        $('#spinner').hide();
    });

    $("#sku_id").on('change', function() {
        $('#spinner').show();
        const $batch_id = $('#batch_id');
        const selSKU = $(this).find(':selected').val();
        var old_batch_id = $('#old_batch_id').val();
        if (selSKU) {
            $.ajax({
                type: 'GET',
                url: '/batch/getBatch/'+selSKU,
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        let opt = '';
                        $batch_id.html('<option value="">-- Select Batch --</option>');
                        $.each(response,function(key,value){
                            let hasSelected = '{{ request()->get('batchList') }}'
                            
                            if (hasSelected === value.id ||  old_batch_id === value.id) {
                                $batch_id.append('<option  value="'+value.id+'" selected>Code: '+value.batch_no + ' @Exp_Date: ' + value.expired_date+'</option>');
                            } else {
                                $batch_id.append('<option value="'+value.id+'">Code: '+value.batch_no + ' @Exp_Date: ' + value.expired_date+'</option>');
                            }
                        });
                    }
                    $('#spinner').hide();
                }
            })
        }
    }).trigger('change');


    $("#batch_id").on('change', function() {
        $('#spinner').show();
        const $serial_id = $('#serial_id');
        const selBatch = $(this).find(':selected').val();
        if (selBatch) {
            $.ajax({
                type: 'GET',
                url: '/product/getProduct/'+selBatch,
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        // let opt = '';
                        let count = 0;
                        var last_serial = '';
                        $serial_id.html('<option value="">-- Add New --</option>');
                        $.each(response,function(key,value){
                            let hasSelected = '{{ request()->get('serial_id') }}'
                            
                            if (hasSelected === value.id) {
                                $serial_id.append('<option selected value="'+value.id+'">'+value.serial_no +'</option>');
                            } else {
                                $serial_id.append('<option  value="'+value.id+'">'+value.serial_no +'</option>');
                            }

                            if(count < 3 ){
                                last_serial = last_serial + "<div class ='badge badge-success p-2 my-1 mx-1'>"+value.serial_no +"</div>";
                                count++;
                            }
                        });
                    }
                    $('#last').append(last_serial);
                    const $serial_no = $('#serial_no');
                    $serial_no.hide();

                    $('#spinner').hide();

                }
            })
        }
    }).trigger('change');

    $("#serial_id").on('change', function() {
        const $serial_no = $('#serial_no');
        const selSerial_text = $(this).find(':selected').text();
        const selSerial_id = $(this).find(':selected').val();

        if (selSerial_id !== "" ) {
            $serial_no.val(selSerial_text);
            $('#add_section').hide();
            $serial_no.show();

        }else{
            $('#add_section').show();
            $serial_no.val('');
            $serial_no.hide();
        };
    }).trigger('change');
    </script>
@stop
