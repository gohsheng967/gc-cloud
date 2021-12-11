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
            @if($data->page_type == 'edit')
            <form method="get" action="{{route('batch',['sku_id' => $data->id])}}" autocomplete="off" >
                <div class="row">
                    <button type="submit" name="goBatch"  class="btn btn-xs btn-primary ml-auto">Show Batch</button>
                </div> 
             </form>
             @endif
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'id')) }}

        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">SKU Code</strong>
                </div>
                <div class="col-sm-3 col-content">
                    {{ Form::text('sku_code', $data->sku_code, array('class' => 'form-control', 'required')) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Category</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('sku_category', $data->sku_category, array('class' => 'form-control', 'required')) }}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Source From</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('source_from', $data->source_from, array('class' => 'form-control', 'required')) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Manufacturer</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('manufacturer', $data->manufacturer, array('class' => 'form-control', 'required')) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Optimal Storage Temp</strong>
                </div>
                <div class="col-sm-1 col-content">
                    {{ Form::text('temperature', $data->temperature, array('class' => 'form-control', 'required')) }}
                </div>
            </div>
            <div id="form-image" class="form-group row ml-1">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Image</strong>
                </div>
                <div class="col-sm-6 col-content">
                    <input class="custom-file-input" name="image" type="file" accept="image/gif, image/jpeg,image/jpg,image/png" data-max-width="800" data-max-height="400">
                    <label class="custom-file-label" for="customFile">Choose file</label>
                    <span class="image-upload-label"><i class="fa fa-question-circle" aria-hidden="true"></i> Please upload the image (Recommended size: 160px × 160px, max 5MB)</span>
                    <div class="image-preview-area">
                        <div id="image_preview" class="image-preview">
                            @if ($data->page_type == 'edit')
                                <img src="{{ asset('uploads/'.$data->image) }}" width="160" title="image"
                                     class="imgP elevation-2">
                            @else
                                <img src="{{ asset('img/no_image_default.png') }}" width="160" title="image"
                                     class="imgP elevation-2">
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

@stop
