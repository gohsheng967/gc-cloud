<?php

namespace App\Http\Controllers\Backend\SKU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use App\Models\Sku;
use Auth;
use Config;
use Crypt;

class SkuController extends Controller
{
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'sku_code',
            'sku_category',
            'manufacturer',
            // 'created_at',
            // 'updated_at',
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(Sku::all())
                ->addColumn('action', function (Sku $data) {
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
                // ->addColumn('download_qr_code', function (Product $data) {
                //     $routeDownloadQr = "https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl=".Crypt::encryptString($data->id)."&choe=UTF-8";
                //     return '<a href="'.$routeDownloadQr.'" target="_blank"><button class="btn btn-outline-primary">Download</button></a>';
                // })
                ->rawColumns(['action'])
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

        return view('backend.sku.index', compact('html'));
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

    private function getRoute()
    {
        return 'sku';
    }

    public function edit($id)
    {
        $data = Sku::find($id);
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Edit';

        return view('backend.sku.form', [
            'data' => $data,
        ]);
    }

    public function delete($id)
    {
        try {
            // Delete
            $new = Sku::find($id);
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
    public function update(Request $request)
    {
        $new = $request->all();
        try {
            $currentData = Sku::find($request->get('id'));
            if ($currentData) {
                $currentData -> sku_code = $request->sku_code;
                $currentData -> sku_category = ucwords(strtolower($request->sku_category));
                $currentData -> source_from = $request->source_from;
                $currentData -> manufacturer = $request->manufacturer;
                $currentData -> temperature = $request->temperature;

                // check delete flag: [name ex: image_delete]
                if ($request->get('image_delete') != null) {
                    $new['image'] = null; // filename for db

                    if ($currentData->{'image'} != 'no_image_default.png') {
                        @unlink(Config::get('const.UPLOAD_PATH') . $currentData['image']);
                    }
                }

                // if new image is being uploaded
                // upload image
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    // image file name example: [id]_image.jpg
                    ${'image'} = $currentData->id . "_sku." . $file->getClientOriginalExtension();
                    // save image to the path
                    $file->move(Config::get('const.UPLOAD_PATH'), ${'image'});
                    $currentData -> image =  ${'image'};
                } else {
                    $currentData -> image = 'no_image_default.png';
                }

                $currentData -> save();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update SKU Details");

                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    public function add(){
        $data = new Sku();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        return view('backend.sku.form', [
            'data' => $data,
        ]);
    }

    public function create(Request $request){
        $new = $request->all();
        try {
            if($new){
                $newSku = new Sku;
                $newSku->sku_code = $request->sku_code;
                $newSku->sku_category = ucwords(strtolower($request->sku_category));
                $newSku->source_from = $request->source_from;
                $newSku->manufacturer = $request->manufacturer;
                $newSku->temperature = $request->temperature;
                $newSku->save();

                if($newSku){
                    if ($request->hasFile('image')) {
                        $file = $request->file('image');
                        // image file name example: [news_id]_image.jpg
                        ${'image'} = $newSku->id . "_sku." . $file->getClientOriginalExtension();
    
                        // save image to the path
                        $file->move(Config::get('const.UPLOAD_PATH'), ${'image'});
                        $newSku->{'image'} = ${'image'};
                    } else {
                        $newSku->{'image'} = 'no_image_default.png';
                    }
                }
                $newSku->save();


                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Created new SKU");

                // Create is successful, back to list
                return back()->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            }
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (Exception $e) {
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }

    }

    
    public function import()
    {
        $data = new Sku();
        $data->form_action = $this->getRoute() . '.importData';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Import';

        return view('backend.sku.import', [
            'data' => $data,
        ]);
    }

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
                if ($countheader == 5 && in_array('sku_code', $header, true)) {
                    // Loop the row data csv
                    while (($csvData = fgetcsv($fp)) !== false) {

                        $csvData = array_map('utf8_encode', $csvData);

                        // Row column length
                        $dataLen = count($csvData);

                        // Skip row if length != 1
                        if (!($dataLen == 5)) {
                            continue;
                        }

                        // Assign value to variables
                        $sku_code = trim($csvData[0]);
                        $sku_category = trim($csvData[1]);
                        $source_from = trim($csvData[2]);
                        $manufacturer = trim($csvData[3]);
                        $temperature = trim($csvData[4]);

                        // Insert data to QR code
                        $dataName = array(
                            'sku_code' => $sku_code,
                            'sku_category' => $sku_category,
                            'source_from' => $source_from,
                            'manufacturer' => $manufacturer,
                            'temperature' => $temperature,
                        );

                        Sku::create($dataName);
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

