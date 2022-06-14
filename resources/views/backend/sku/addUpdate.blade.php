@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update SKU ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Stock Keeping Unit</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add or Update</h3>
            <div class="spinner-border float-right hide" role="status" id ='spinner'>
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'id')) }}

        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">SKU Code</strong>
                </div>
                <div class="col-sm-3 col-content">
                    <!-- {{ Form::text('sku_code', $data->sku_code, array('class' => 'form-control', 'required')) }} -->
                    <select name="sku_id" id="sku_id" class = "form-control">
                        <option value="add">Add New SKU</option>
                        @foreach($data->extSku as $sku)
                            <option value="{{$sku->id}}">{{$sku->sku_code}}</option>
                        @endforeach
                    </select>
                    {{ Form::text('sku_code', $data->sku_code, array('class' => 'form-control mt-1', 'id' =>'sku_code' )) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Halal Cert</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('halal_cert_no', $data->halal_cert_no, array('class' => 'form-control', 'id' =>'halal_cert_no', 'maxlength'=> '19')) }}
                    <small>SAMPLE: JAKIM-X XXX-XX/XXXX</small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Category</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('sku_category', $data->sku_category, array('class' => 'form-control', 'required', 'id' =>'sku_category' )) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Source From</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('source_from', $data->source_from, array('class' => 'form-control', 'required',  'id' =>'source_from')) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Manufacturer</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('manufacturer', $data->manufacturer, array('class' => 'form-control', 'required', 'id' =>'manufacturer')) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Optimal Storage Temp</strong>
                </div>
                <div class="col-sm-1 col-content">
                    {{ Form::text('temperature', $data->temperature, array('class' => 'form-control', 'required', 'id' =>'temperature')) }}
                </div>
            </div>
            <div id="form-image" class="form-group row ml-1">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Image</strong>
                </div>
                <div class="col-sm-6 col-content">
                    <input class="custom-file-input" name="image" type="file" accept="image/gif, image/jpeg,image/jpg,image/png" data-max-width="800" data-max-height="400" id ="fileInput">
                    <label class="custom-file-label" for="customFile" id = "fileUpload">Choose file</label>
                    <span class="image-upload-label"><i class="fa fa-question-circle" aria-hidden="true"></i> Please upload the image (Recommended size: 160px × 160px, max 5MB)</span>
                    <div class="image-preview-area">
                        <div id="image_preview" class="image-preview">
                            @if ($data->page_type == 'edit')
                                <img src="{{ asset('uploads/'.$data->image) }}" width="160" title="image"
                                     class="imgP elevation-2"  id ="imgDisplay">
                            @else
                                <img src="{{ asset('img/no_image_default.png') }}" width="160" title="image"
                                     class="imgP elevation-2"  id ="imgDisplay">
                            @endif
                        </div>
                        {{-- only image has main image, add css class "show" --}}
                        <p class="delete-image-preview @if ($data->image != null && $data->image != 'default-user.png') show @endif"
                            onclick="deleteImagePreview(this);"><i class="fa fa-window-close"></i></p>
                        {{-- delete flag for already uploaded image in the server --}}
                        <input name="image_delete" type="hidden">
                    </div>
                </div>
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
    $("#sku_id").on('change', function() {
        const $batch_id = $('#batch_id');
        const selSKU = $(this).find(':selected').val();
        $('#spinner').show();
        if (selSKU !== "add" && selSKU !== "" ) {
            $.ajax({
                type: 'GET',
                url: '/sku/getSku/'+selSKU,
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        $.each(response,function(key,value){
                            $('#sku_category').val(value.sku_category);
                            $('#source_from').val(value.source_from);
                            $('#halal_cert_no').val(value.halal_cert_no);
                            $('#manufacturer').val(value.manufacturer);
                            $('#temperature').val(value.temperature);
                            $('#sku_code').val(value.sku_code);
                            $('#sku_code').prop('required',true);
                            $("#imgDisplay").attr("src", '/uploads/'+value.image);
                            setTimeout(function() {
                                $('#spinner').hide();
                            }, 500);
                        });
                    }
                }
            })
        }else{
            $('#sku_category').val('');
            $('#source_from').val('');
            $('#manufacturer').val('');
            $('#temperature').val('');
            $('#halal_cert_no').val('');
            $('#sku_code').show();
            $('#sku_code').prop('required',true);
            setTimeout(function() {
                $('#spinner').hide();
            }, 500);
            
        };
    }).trigger('change');

    $('#fileInput').change(function() {
            var file = $('#fileInput')[0].files[0].name;
            $('#fileUpload').text(file);
        });
    </script>

@stop
