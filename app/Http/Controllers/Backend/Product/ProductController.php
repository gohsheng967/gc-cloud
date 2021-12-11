<?php

namespace App\Http\Controllers\backend\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sku;
use App\Models\User;
use App\Models\History;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use Config;
use Crypt;
use Auth;
use Log;
use Yajra\Datatables\Datatables;

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
                    $routeDownloadQr = "https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl=".Crypt::encryptString($data->id)."&choe=UTF-8";
                    return '<a href="'.$routeDownloadQr.'" target="_blank"><button class="btn btn-outline-primary">Download</button></a>';
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
        $data->button_text = 'Add';
        $skuList = SKU::all();

        return view('backend.product.form', [
            'data' => $data, 'skuList'=>$skuList
        ]);
    }

    public function create(Request $request){
        $new = $request->all();
        try {
            if($new){
                $newProduct = new Product;
                $newProduct->sku_id = $request->sku_id;
                $newProduct->batch_id = $request->batch_id;
                $newProduct->serial_no = $request->serial_no;
                $newProduct->save();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Created new Product QR");

                // Create is successful, back to list
                return redirect()->route('product')->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            }
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (Exception $e) {
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
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
            [
                'extend' => 'csvHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'pdfHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'excelHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'print',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
        ];
    }

    public function delete($id)
    {
        try {
            // Delete
            $new = Product::find($id);
            $new->delete();

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
        $data->button_text = 'Edit';
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

                        // Skip row if length != 1
                        if (!($dataLen == 4)) {
                            continue;
                        }

                        // Assign value to variables
                        $sku_code = trim($csvData[0]);
                        $batch_no = trim($csvData[1]);
                        $serial_no = trim($csvData[2]);

                        $sku_id = Sku::select('id')->where('sku_code', $sku_code)->first();
                        $batch_id = Sku::select('id')->where('batch_no', $batch_no)->first();
                        if(empty($sku_id) || empty($batch_id)){
                            return redirect()->route($this->getRoute())->with('error', 'Import failed! Invalid Data.');
                        }
                        // Insert data to QR code
                        $dataName = array(
                            'sku_id' => $sku_code,
                            'batch_id' => $batch_no,
                            'serial_no' => $serial_no,
                        );

                        Product::create($dataName);
                    }

                    return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
                }
                return redirect()->route($this->getRoute())->with('error', 'Import failed! You are using the wrong CSV format. Please use the CSV template to import your data.');
            }
            return redirect()->route($this->getRoute())->with('error', 'Please choose file with .CSV extension.');
        }

        return redirect()->route($this->getRoute())->with('error', 'Please select CSV file.');
    }

}
