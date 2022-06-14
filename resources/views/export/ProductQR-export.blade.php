<table>
    <thead>
    <tr>
    <th style ="width:100px;text-align:center">SKU Code</th>
        <th style ="width:100px;text-align:center">Batch No</th>
        <th style ="width:100px;text-align:center" >Serial No</th>
        <th style ="width:100px;text-align:center" >QrCode</th>


    </tr>
    </thead>
    <tbody>
    @foreach($data as $dt)
        <tr>
            <td style ="text-align:center">{{$dt->skuCode->sku_code}}</td>
            <td style ="text-align:center">{{$dt->batchNo->batch_no}}</td>
            <td style ="text-align:center">{{$dt->serial_no}}</td>
            <td style ="text-align:center;height:100px"><img src = "{{$dt->qrCode_img }}" width ='auto' height ='90px' style ="padding-left=3px" /></td>
        </tr>
 
    @endforeach
    </tbody> 
</table>
