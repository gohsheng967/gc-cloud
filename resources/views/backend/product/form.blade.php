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
                <div class="col-sm-3 col-content">
                    {{ Form::text('serial_no', $data->serial_no, array('class' => 'form-control', 'required')) }}
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
    $("#sku_id").on('change', function() {
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
                                $batch_id.append('<option selected value="'+value.id+'">Code: '+value.batch_no + ' @Exp_Date: ' + value.expired_date+'</option>');
                            } else {
                                $batch_id.append('<option value="'+value.id+'">Code: '+value.batch_no + ' @Exp_Date: ' + value.expired_date+'</option>');
                            }
                        });
                    }
                }
            })
        }
    }).trigger('change')
    </script>
@stop
