<?php

namespace App\Http\Controllers\backend\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Sku;
use App\Models\User;
use App\Models\History;
use App\Models\Batch;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use Config;
use Crypt;
use Auth;
use Log;
use Yajra\Datatables\Datatables;
use Storage;
use App\Exports\ProductQRCodeExcelExport;
use Excel;
use Validator;
use PDF;
use Illuminate\Support\Str;





class ProductController extends Controller
{
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'serial_no',
            // 'ladang_ternakan',
            // 'kilang_proses',
            // 'baik_sebelum',
            'download_qr_code',
            'created_at',
            'updated_at',
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(Product::all())
                ->addColumn('action', function (Product $data) {
                    $routeEdit = route($this->getRoute() . ".edit", $data->id);
                    $routeDelete = route($this->getRoute() . ".delete", $data->id);

                    $button = '<div class="col-sm-12"><div class="row">';
                    $button .= '<div class="col-sm-4"><a href="'.$routeEdit.'"><button class="btn btn-primary"><i class="fa fa-edit"></i></button></a></div> ';
                    if (Auth::user()->hasRole('administrator')) { // Check the role
                        $button .= '<div class="col-sm-4"><a href="'.$routeDelete.'" class="delete-button"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a></div>';
                    } else {
                        $button .= '<div class="col-sm-4 "><a href="#"><button class="btn btn-danger disabled"><i class="fa fa-trash"></i></button></a></div>';
                    }
                    $button .= '</div></div>';
                    return $button;
                })
                ->addColumn('download_qr_code', function (Product $data) {
                    // $routeDownloadQr = "https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl=".Crypt::encryptString($data->id)."&choe=UTF-8";
                    // return '<a href="'.$routeDownloadQr.'" target="_blank"><button class="btn btn-outline-primary">Download</button></a>';
                    return '<a href="'.$data->qrCode_img.'" target="_blank"><button class="btn btn-outline-primary">Download</button></a>';

                    // return "<img src='".$data->qrCode_img."' alt='' width ='200px'/>";
                })
                ->rawColumns(['action', 'download_qr_code'])
                ->toJson();
        }

        $columnsArrExPr = [1,3,4];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[1,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.product.index', compact('html'));
    }


    public function add(){
        $data = new Product();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Save';
        $skuList = SKU::all();

        return view('backend.product.addUpdate', [
            'data' => $data, 'skuList'=>$skuList
        ]);
    }

    public function addUpdate(Request $request){
        $new = $request->all();

        if($request->sku_id == ""){
            return  back()->withInput()->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));  
        }

        if($request->batch_id == ""){
            return  back()->withInput()->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));  
        }


        if(empty($request->serial_id)){


            try {
                if($new){

                    $this->validate($request, [
                        'quantity' => 'required',
                        'batch_id' => 'required',
                        'sku_id' => 'required',
                        'batch_id' => 'required',
                        'prefix_1' => 'required',
                        'running_no' => 'required',
                    ],[],[
                    ]);

                    $prefix = $request->prefix_1;
                    $running_no = $request->running_no;

                    if(!preg_match("/^[0-9]+$/", $running_no)){
                        return  back()->withInput()->with('error','Non integer Character Found');  
                    }
                    $length = strlen($running_no);
                    $convert_to_int = intval($running_no);
                    $i = 1;

                    while($i <= $request->quantity){


                        $newProduct = new Product;
                        $newProduct->sku_id = $request->sku_id;
                        $newProduct->batch_id = $request->batch_id;
                        $newProduct->serial_no =  $prefix.str_pad($convert_to_int, $length, '0', STR_PAD_LEFT);
                        $newProduct->uuid = Str::uuid();
                        $newProduct->save();
    
                        $newProduct->refresh();
                        // dd(storage_path());
                        $path = "QRCode_".$newProduct->uuid.".png";
    
                        // log::debug(Crypt::encryptString($newProduct->id));
    
                        $newProduct-> encrypt = Crypt::encryptString($newProduct->id);
                                $newProduct->save();
    
                        \QrCode::size(500)->format('png')->encoding('UTF-8')->generate($newProduct-> encrypt, storage_path()."/app/public/QrCode/". $path);
    
                        $newProduct -> qrCode_img = "storage/QrCode/".$path;
                        $newProduct->save();
    
    
                        // // Save log
                        $controller = new SaveActivityLogController();
                        $controller->saveLog($new, "Created new Product QR");

                        $convert_to_int =  $convert_to_int +1;
                        $i+=1;
                    }


                    
    
                    // Create is successful, back to list
                    return redirect()->route('product')->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
                }
                // Create is failed
                return back()->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
            } catch (Exception $e) {
                // Create is failed
                return  back()->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
            }
        }else{
            $this->validate($request, [
                'serial_no' => 'required|unique:product',
                'batch_id' => 'required',
                'sku_id' => 'required',
            ],[],[
            ]);

            $record = Product::where('sku_id', $request->sku_id)
                             ->where('batch_id', $request->batch_id)
                             ->where('id', $request->serial_id)
                            ->first();
            if(empty($record)){
                return  back()->with('error', 'Product Not Found');
            }

            $record->serial_no = $request->serial_no;
            $record ->save();

            return redirect()->route('product')->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));

        }


    }
    private function getRoute()
    {
        return 'product';
    }

    public function buttonDatatables($columnsArrExPr)
    {
        return [
            [
                'pageLength'
            ],
           // [
            //    'extend' => 'csvHtml5',
            //    'exportOptions' => [
            //        'columns' => $columnsArrExPr
            //    ]
         //   ],
          //  [
          //      'extend' => 'pdfHtml5',
          //      'exportOptions' => [
          //          'columns' => $columnsArrExPr
         //       ]
         //   ],
         //   [
         //       'extend' => 'excelHtml5',
         //       'exportOptions' => [
         //           'columns' => $columnsArrExPr
         //       ]
         //   ],
         //   [
         //       'extend' => 'print',
         //       'exportOptions' => [
         //           'columns' => $columnsArrExPr
         //       ]
          //  ],
        ];
    }

    public function delete($id)
    {
        try {
            // Delete
            $new = Product::find($id);
            $new->delete();

            $record = ProductReport::where('scan_product_id', $id)->get();
            foreach($record as $row){
                $row->delete();
            }

            // Save log
            $controller = new SaveActivityLogController();
            $controller->saveLog($new->toArray(), "Delete history");

            //delete success
            return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_MESSAGE'));
        } catch (Exception $e) {
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_MESSAGE'));
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->getRoute())->with('error', Config::get('const.ERROR_FOREIGN_KEY'));
        }
    }

    public function edit($id)
    {
        $data = Product::find($id);
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Save';
        $skuList = SKU::all();

        return view('backend.product.form', [
            'data' => $data, 'skuList'=>$skuList
        ]);
    }

    public function update(Request $request)
    {
        $new = $request->all();
        try {
            $currentData = Product::find($request->get('id'));
            if ($currentData) {
                // $this->validator($new, 'update')->validate();
                $currentData -> sku_id = $request->sku_id;
                $currentData -> batch_id = $request->batch_id;
                $currentData -> serial_no = $request->serial_no;

                $currentData -> save();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update product QR");

                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    public function import()
    {
        $data = new Product();
        $data->form_action = $this->getRoute() . '.importData';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Import';

        return view('backend.product.import', [
            'data' => $data,
        ]);
    }

    /**
     * Upload and import data from csv file.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function importData(Request $request)
    {
        // If file extension is 'csv'
        if ($request->hasFile('import')) {
            $file = $request->file('import');

            // File Details
            $extension = $file->getClientOriginalExtension();
            
            // If file extension is 'csv'
            if ($extension == 'csv') {
                $fp = fopen($file, 'rb');

                $header = fgetcsv($fp, 0, ',');
                $countheader = count($header);
                // Check is csv file is correct format
                if ($countheader == 3 && in_array('sku_code', $header, true)) {
                    // Loop the row data csv
                    while (($csvData = fgetcsv($fp)) !== false) {

                        $csvData = array_map('utf8_encode', $csvData);

                        // Row column length
                        $dataLen = count($csvData);

                        // Skip row if length != 3
                        if (!($dataLen == 3)) {
                            continue;
                        }

                        // Assign value to variables
                        $sku_code = trim($csvData[0]);
                        $batch_no = trim($csvData[1]);
                        $serial_no = trim($csvData[2]);

                        $sku_id = Sku::select('id')->where('sku_code', $sku_code)->first();
                        $batch_id = Batch::select('id')->where('batch_no', $batch_no)->where('sku_id', $sku_id->id)->first();
			$uuid = Str::uuid();
                        if(!empty($sku_id) && !empty($batch_id)){
			    $check = Product::where('sku_id',$sku_id->id)->where('batch_id', $batch_id->id)->where('serial_no', $serial_no)->first();
			    if(empty($check)){
                            $dataName = array(
                                'sku_id' => $sku_id->id,
                                'batch_id' => $batch_id->id,
                                'serial_no' => $serial_no,
				'uuid' => $uuid
                            );
    
                            $product = Product::create($dataName);
    
                            $path = "QRCode_".$product->uuid.".png";
                            
			    $product-> encrypt = Crypt::encryptString($product->id);
                            $product->save();

                            \QrCode::size(500)->format('png')->encoding('UTF-8')->generate($product-> encrypt, storage_path()."/app/public/QrCode/". $path);
    
                            $product -> qrCode_img = "storage/QrCode/".$path;
                            $product->save();                        
                        }
			}
                    }

                    return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
                }
                return redirect()->route($this->getRoute())->with('error', 'Import failed! You are using the wrong CSV format. Please use the CSV template to import your data.');
            }
            return redirect()->route($this->getRoute())->with('error', 'Please choose file with .CSV extension.');
        }

        return redirect()->route($this->getRoute())->with('error', 'Please select CSV file.');
    }

    public function getProduct(Request $request, $id){
        $product = Product::where('batch_id',$id)->get();
            return response()->json($product);        
    }

    public function exportPage(Request $request){     
        
        $skus = SKU::all();

        $filter = $request->filter_sku;

        $data = Product::when($filter, function ($query) use ($filter) {
            return $query->where('sku_id', $filter);
        })->get();
        

        return view('backend.product.export', compact('skus', 'data'));
    }

    public function exportProductQRExcel(Request $request){


        // $arrayProductID = $request->arrayExport;

        $arrayProductID = explode(",", $request->arrayExport);


        if(!empty($arrayProductID)){
            $product = Product::whereIn('id', $arrayProductID)->get();

            return Excel::download(new ProductQRCodeExcelExport($product), 'ProductQrCode'.'.xlsx');

        }

    }

    public function exportProductQRPDF(Request $request){


        // $arrayProductID = $request->arrayExport;

        $arrayProductID = explode(",", $request->arrayExport);


        if(!empty($arrayProductID)){
            $product = Product::whereIn('id', $arrayProductID)->get();

            $products = [];
            $count = 1 ;
            $html = '';

            foreach( $product  as $item){
                // $nestedData['serial_no'] = $item->serial_no; 
                // $nestedData['qrCode_img'] = $item->qrCode_img; 


                // $nestedData['nextLine'] = $item->qrCode_img; 

                // $products[] = $nestedData;
            if($count < 4){
                if($count == 1){
                    $html = $html."<tr><td><div style='width:300px;padding:10px'><img src= '".$item->qrCode_img."' alt='' width='300px'/><br><p style ='text-align:center'>".$item->serial_no."</p></div></td>";
                    $count = $count + 1;
                }else if($count == 3){
                    $html = $html."<td><div style='width:300px;padding:10px'><img src= '".$item->qrCode_img."' alt='' width='300px'/><br><p style ='text-align:center'>".$item->serial_no."</p></div></td></tr>";
                    $count = $count + 1;
                }
                else{
                    $html = $html."<td><div style='width:300px;padding:10px'><img src= '".$item->qrCode_img."' alt='' width='300px'/><br><p style ='text-align:center'>".$item->serial_no."</p></div></td>";
                    $count = $count + 1;
                }

            }else{
                $html = $html."<tr><div style='width:300px;padding:10px'><img src= '".$item->qrCode_img."' alt='' width='300px'/><br><p style ='text-align:center'>".$item->serial_no."</p></div>";
                $count = -1;
            }

        }
            $pdf = PDF::loadView('backend.product.productQRCode_pdf', compact('html'))->setPaper('A3', 'Portrait');

            return $pdf->download('ProductQrCode.pdf');

        }

    }
}
