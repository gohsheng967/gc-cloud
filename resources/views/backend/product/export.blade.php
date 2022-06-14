{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Product Qr  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Product Qr</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <form action="{{route('product.exportPage')}}">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter</h3>
        </div>
        <div class="card-body row">
            <div class="col-sm-4">
                <label class="form-label">{{ __('SKU') }}</label>
                <select class="form-control" name="filter_sku" id="selVariant">
                    <option value="" selected>-- All --</option>
                    @if(!empty($skus))
                    @foreach ($skus as $sku)
                    <option data-id="{{ $sku->id }}" value="{{ $sku->id }}" {{ request()->get('filter_sku') == $sku->id ? 'selected': ''}}>{{ $sku->sku_code }}</option>
                    @endforeach
                    @endif
                </select>
                <button type = 'submit' class ='btn btn-primary my-3'>Search</button>
            </div>
        </div>
    </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List</h3>
            <br>
            <button id ='exportPDF' class ='btn btn-info btn-lg my-3'>Export to PDF</button>
            <button id ='exportExcel' class ='btn btn-info btn-lg my-3'>Export to Excel</button>

        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col"><input type="checkbox" onclick="checkAll(this);"></th>
                            <th scope="col">SKU Code</th>
                            <th scope="col">Batch No</th>
                            <th scope="col">Serial No</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($data))
                        @foreach($data as $row)
                        <tr>
                            <td><input type="checkbox" class ='checboxArray' value ='{{$row->id}}' ></td>
                            <td>{{$row->skuCode->sku_code}}</td>
                            <td>{{$row->batchNo->batch_no}}</td>
                            <td>{{$row->serial_no}}</td>
                        </tr>
                        @endforeach
                        @else
                        No Data Found
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet">
@stop

@section('js')
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
    <script src="{{ asset('js/main_index.js'). '?v=' . rand(99999,999999) }}"></script>
@stop

@push('js')
<script>

function checkAll(e)
{
    if(!$(e).is(':checked')){
        $('input[type=checkbox]').each(
            function (index, checkbox) {
            if (index != 0) {
                checkbox.checked = false;
            }
        });
    }else{
        $('.checboxArray').prop("checked", "true");
    }

}

$( "#exportExcel" ).click(function() {

    var checked = $('.checboxArray:checkbox:checked');
    var i = 0;
    var arrayExport =[];

    while(checked.length > i){

        arrayExport.push(checked[i].value);

        i++;
    }

    console.log(arrayExport);

    if(arrayExport.length != 0){
       

        var url = "{{route('exportProductQRExcel')}}?arrayExport=" + arrayExport;
        console.log(url);
        window.location = url;
    }
});


$( "#exportPDF" ).click(function() {

var checked = $('.checboxArray:checkbox:checked');
var i = 0;
var arrayExport =[];

while(checked.length > i){

    arrayExport.push(checked[i].value);

    i++;
}

console.log(arrayExport);

if(arrayExport.length != 0){
   

    var url = "{{route('exportProductQRPDF')}}?arrayExport=" + arrayExport;
    console.log(url);
    window.location = url;
}
});
    
</script>

@endpush
