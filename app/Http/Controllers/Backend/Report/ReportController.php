<?php

namespace App\Http\Controllers\Backend\Report;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Report;
use Auth;
use Config;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * More info DataTables : https://yajrabox.com/docs/laravel-datatables/master
     *
     * @param Datatables $datatables
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Datatables $datatables, Request $request)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'name' => ['name' => 'user.name'],
            'date',
            'in_time',
            'out_time',
            'total_hour',
            'in_location' => ['name' => 'in_history.name'],
            'out_location' => ['name' => 'out_history.name']
        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        if ($datatables->getRequest()->ajax()) {
            $query = Report::with('in_history', 'out_history', 'user')
                ->select('reports.*');

            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            // visitor
            if (Auth::user()->hasRole('visitor')) {
                // There is any visitor
                $query = $query->where('user_id', Auth::user()->id);
            }

            return $datatables->of($query)
                ->addColumn('name', function (Report $data) {
                    return $data->user->name;
                })
                ->addColumn('in_location', function (Report $data) {
                    return $data->in_location_id == null ? '' : $data->in_history->name;
                })
                ->addColumn('out_location', function (Report $data) {
                    return $data->out_location_id == null ? '' : $data->out_history->name;
                })
                ->rawColumns(['name', 'in_location', 'out_location'])
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
