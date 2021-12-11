<?php

namespace App\Http\Controllers\backend\ProductReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\ProductReport;
use Auth;

class ProductReportController extends Controller
{
    public function index(Datatables $datatables, Request $request)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'name' => ['name' => 'user.name'],
            'scan_time',
            'product'  =>['product' => 'scan_product_id'],
            'sku'  =>['sku' => 'sku_id'],
            'batch'  =>['batch' => 'batch_id']


        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        if ($datatables->getRequest()->ajax()) {
            $query = ProductReport::with('product_scan', 'user')
                ->select('product_report.*');

            if ($from && $to) {
                $query = $query->whereBetween('scan_time', [$from, $to]);
            }

            // visitor
            if (Auth::user()->hasRole('visitor')) {
                // There is any visitor
                $query = $query->where('user_id', Auth::user()->id);
            }
            
            return $datatables->of($query)
                ->addColumn('name', function (ProductReport $data) {
                    return $data->user->name;
                })
                ->addColumn('product', function (ProductReport $data) {
                    return $data->product_scan->serial_no;
                })
                ->addColumn('sku', function (ProductReport $data) {
                    return $data->product_scan->skuCode->sku_code;
                })
                ->addColumn('batch', function (ProductReport $data) {
                    return $data->product_scan->batchNo->batch_no;
                })
                ->rawColumns(['name', 'product','sku' ])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc'], [2,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.reports.index', compact('html'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
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

    /**
     * Get script for the date range.
     *
     * @return string
     */
    public function scriptMinifiedJs()
    {
        // Script to minified the ajax
        return <<<CDATA
            var formData = $("#date_filter").find("input").serializeArray();
            $.each(formData, function(i, obj){
                data[obj.name] = obj.value;
            });
CDATA;
    }
}
