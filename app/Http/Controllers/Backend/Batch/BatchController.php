<?php

namespace App\Http\Controllers\Backend\Batch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Sku;
use App\Models\Batch;
use App\Models\Product;
use Auth;
use Config;
use Crypt;
use Log;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;

class BatchController extends Controller
{
    public function index(Datatables $datatables, $sku_id)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'batch_no',
            'delivery_date',
            'expired_date',
            // 'created_at',
            // 'updated_at',
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(Batch::where('sku_id',$sku_id )->get())
                ->addColumn('action', function (Batch $data) {
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
        $sku = $sku_id;
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

        return view('backend.batch.index', compact('html', 'sku'));
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
        return 'batch';
    }

    private function getRouteProduct()
    {
        return 'product';
    }

    public function edit(Datatables $datatables, $id)
    {
        $data = Batch::find($id);
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Edit';

        $sku_code = Sku::find($data->sku_id);
        $data->sku_code =$sku_code->sku_code;

        // Product Serial Number
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'serial_no',
            'download_qr_code',
            'created_at',
            'updated_at',
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(Product::where('sku_id', $data->sku_id)->where('batch_id', $id)->get())
                ->addColumn('action', function (Product $data) {
                    $routeEdit = route($this->getRouteProduct() . ".edit", $data->id);
                    $routeDelete = route($this->getRouteProduct() . ".delete", $data->id);

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


        return view('backend.batch.form', [
            'data' => $data,
            'html' =>$html
        ]);
    }

    public function delete($id)
    {
        try {
            // Delete
            $new = Batch::find($id);
            $new->delete();

            // Save log
            $controller = new SaveActivityLogController();
            $controller->saveLog($new->toArray(), "Delete Batch");

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
            $currentData = Batch::find($request->get('id'));
            if ($currentData) {
                $currentData -> batch_no = $request->batch_no;
                $currentData -> delivery_date = $request->delivery_date;
                $currentData -> expired_date = $request->expired_date;
                $currentData -> remark = $request->remark;

                $currentData -> save();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update Batch Details");

                return redirect()->route($this->getRoute(),$currentData->sku_id)->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    public function add($sku_id){
        $data = new Batch();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        $sku_code = Sku::find($sku_id);
        $data->sku_code =$sku_code->sku_code;
        $data->sku_id =$sku_id;
        $html = "";
        return view('backend.batch.form', [
            'data' => $data, 'html' =>$html
        ]);
    }

    public function create(Request $request){
        $new = $request->all();
        try {
            if($new){
                $newBatch = new Batch;
                $newBatch->sku_id = $request->sku_id;
                $newBatch->batch_no = $request->batch_no;
                $newBatch->delivery_date = $request->delivery_date;
                $newBatch->expired_date = $request->expired_date;
                $newBatch->remark = $request->remark;
                $newBatch->save();

                $newBatch->save();


                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Created new Batch");

                // Create is successful, back to list
                return redirect()->route('batch',['sku_id'=>$request->sku_id])->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            }
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (Exception $e) {
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }

    }

    public function getBatch($sku_id){
        $batchList = Batch::where('sku_id',$sku_id)->get();
        
        return response()->json($batchList);
        
    }
}
