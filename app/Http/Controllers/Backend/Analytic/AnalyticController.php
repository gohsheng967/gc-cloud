<?php

namespace App\Http\Controllers\Backend\Analytic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReport;
use Auth;
use Config;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use File;
use Log;
class AnalyticController extends Controller
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
     * Show the data as chart.
     * More info Library : https://github.com/fxcosta/laravel-chartjs
     * More info ChartJs : https://www.chartjs.org/
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    // public function index(Request $request)
    // {
    //     $from = isset($request->from) ? Carbon::parse($request->from)->startOfDay() : '';
    //     $to = isset($request->to) ? Carbon::parse($request->to)->endOfDay() : '';
    //     $param['from'] = $from != '' ? Carbon::parse($from)->format('Y-m-d') : '';
    //     $param['to'] = $to != '' ? Carbon::parse($to)->format('Y-m-d') : '';

    //     $gerDataAnalytics = Report::query();
    //     $gerDataAnalytics = $gerDataAnalytics->select(
    //         DB::raw("DATE_FORMAT(date, '%M %d, %Y') as label"),
    //         DB::raw('TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(total_hour))), "%H") as countTotalHour')
    //     );

    //     if ($param['from'] && $param['to']) {
    //         $dateArrFrom =  Carbon::parse($param['from'])->startOfDay();
    //         $dateArrTo =  Carbon::parse($param['to'])->endOfDay();
    //         $gerDataAnalytics = $gerDataAnalytics->whereBetween('date', [$param['from'], $param['to']]);
    //     } else {
    //         $dateArrFrom =  Carbon::parse(Carbon::now()->firstOfMonth())->startOfDay();
    //         $dateArrTo =  Carbon::parse(Carbon::now()->lastOfMonth())->endOfDay();
    //         $gerDataAnalytics = $gerDataAnalytics->whereBetween('date', [Carbon::now()->firstOfMonth(), Carbon::now()->lastOfMonth()]);
    //     }

    //     $gerDataAnalytics = $gerDataAnalytics->groupBy('date');
    //     $gerDataAnalytics = $gerDataAnalytics->get();

    //     // Generate date with CarbonPeriod
    //     $daysOfMonth = collect(
    //         CarbonPeriod::create(
    //             $dateArrFrom,
    //             $dateArrTo
    //         )
    //     )
    //         ->map(function ($gerDataAnalytics) {
    //             return [
    //                 'label' => $gerDataAnalytics->format('F d, Y'),
    //                 'countTotalHour' => 0,
    //             ];
    //         })
    //         ->keyBy('label')
    //         ->merge(
    //             $gerDataAnalytics->keyBy('label')
    //         )
    //         ->values();

    //     // $returnData['label'] = [];
    //     // $returnData['dataSum'] = [];

    //     foreach ($daysOfMonth as $value) {
    //         $returnData['label'][] = $value['label'];
    //         $returnData['dataTotalHour'][] = (int)$value['countTotalHour'];
    //     }

    //     $analytic = $this->chartAnalytics('analyticHistories', "Analytic", $returnData);

    //     return view('backend.analytic.index', compact('analytic', 'param'));
    // }

    /**
     * Function show chart.
     *
     * @param $name
     * @param $title title of chartjs
     * @param $data
     * @return data
     */
    public function chartAnalytics($name, $title, $data)
    {
        $chartjs = app()->chartjs
            ->name($name)
            ->type('line')
            ->size(['width' => 800, 'height' => 500])
            ->labels($data['label'])
            ->datasets([
                [
                    "label" => "Total Hour",
                    'borderDash' => [5, 5],
                    'pointRadius' => true,
                    'backgroundColor' => "rgba(255, 34, 21, 0.31)",
                    'borderColor' => "rgba(255, 34, 21, 0.7)",
                    "pointColor" => "rgba(255, 34, 21, 0.7)",
                    "pointStrokeColor" => "rgba(255, 34, 21, 0.7)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHighlightStroke" => "rgba(220,220,220,1)",
                    'data' => $data['dataTotalHour']
                ],
                // [
                //     "label" => "Overtime Work",
                //     'backgroundColor' => 'rgba(210, 214, 222, 1)',
                //     'borderColor' => 'rgba(210, 214, 222, 1)',
                //     'pointRadius' => true,
                //     "pointColor" => 'rgba(210, 214, 222, 1)',
                //     "pointStrokeColor" => '#c1c7d1',
                //     "pointHighlightFill" => "#fff",
                //     "pointHighlightStroke" => 'rgba(220,220,220,1)',
                //     'data' => $data['dataOver']
                // ],
                // [
                //     "label" => "Early Out Time",
                //     'backgroundColor' => 'rgba(60,141,188,0.9)',
                //     'borderColor' => 'rgba(60,141,188,0.8)',
                //     'pointRadius' => true,
                //     "pointColor" => '#3b8bba',
                //     "pointStrokeColor" => 'rgba(60,141,188,1)',
                //     "pointHighlightFill" => "#fff",
                //     "pointHighlightStroke" => 'rgba(60,141,188,1)',
                //     'data' => $data['dataEarlyOut']
                // ],
            ])
            ->options([]);

        $chartjs->optionsRaw([
            'title' => [
                'text' => $title,
                'display' => true,
                'position' => "top",
                'fontSize' => 18,
                'fontColor' => "#000"
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'legend' => [
                'position' => 'top',
            ],
            'scales' => [
                'xAxes' => [
                    [
                        'gridLines' => [
                            'display' => false
                        ]
                    ]
                ],
                'yAxes' => [
                    [
                        'gridLines' => [
                            'display' => false
                        ]
                    ]
                ],
            ]
        ]);

        return $chartjs;
    }

    public function index(Request $request)
    {
        $from = isset($request->from) ? Carbon::parse($request->from)->startOfDay() : '';
        $to = isset($request->to) ? Carbon::parse($request->to)->endOfDay() : '';
        $param['from'] = $from != '' ? Carbon::parse($from)->format('Y-m-d') : '';
        $param['to'] = $to != '' ? Carbon::parse($to)->format('Y-m-d') : '';

        // $gerDataAnalytics = ProductReport::query();
        // $gerDataAnalytics = $gerDataAnalytics->select(
        //     DB::raw("DATE_FORMAT(date, '%M %d, %Y') as label"),
        //     DB::raw('TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(total_hour))), "%H") as countTotalHour')
        // );
        
        $gerDataAnalytics = ProductReport::query();
        $gerDataAnalytics = $gerDataAnalytics
                ->select(
                    DB::raw("DATE_FORMAT(scan_time, '%M %d, %Y') as label"),
                    DB::Raw('COUNT(*) countTotalHour')
                );

        if ($param['from'] && $param['to']) {
            $dateArrFrom =  Carbon::parse($param['from'])->startOfDay();
            $dateArrTo =  Carbon::parse($param['to'])->endOfDay();
            $gerDataAnalytics = $gerDataAnalytics->whereBetween('scan_time', [$param['from'], $param['to']]);
        } else {
            $dateArrFrom =  Carbon::parse(Carbon::now()->firstOfMonth())->startOfDay();
            $dateArrTo =  Carbon::parse(Carbon::now()->lastOfMonth())->endOfDay();
            $gerDataAnalytics = $gerDataAnalytics->whereBetween('scan_time', [Carbon::now()->firstOfMonth(), Carbon::now()->lastOfMonth()]);
        }

        $gerDataAnalytics = $gerDataAnalytics->groupBy(DB::raw("DATE_FORMAT(scan_time, '%M %d, %Y')"));
        $gerDataAnalytics = $gerDataAnalytics->get();
        // log::debug($gerDataAnalytics);
        // Generate date with CarbonPeriod
        $daysOfMonth = collect(
            CarbonPeriod::create(
                $dateArrFrom,
                $dateArrTo
            )
        )
            ->map(function ($gerDataAnalytics) {
                return [
                    'label' => $gerDataAnalytics->format('F d, Y'),
                    'countTotalHour' => 0,
                ];
            })
            ->keyBy('label')
            ->merge(
                $gerDataAnalytics->keyBy('label')
            )
            ->values();
        // $returnData['label'] = [];
        // $returnData['dataSum'] = [];

        foreach ($daysOfMonth as $value) {
            $returnData['label'][] = $value['label'];
            $returnData['dataTotalHour'][] = (int)$value['countTotalHour'];
        }

        $analytic = $this->chartProductAnalytics('analyticHistories', "Analytic", $returnData);

        return view('backend.analytic.index', compact('analytic', 'param'));
    }


    public function chartProductAnalytics($name, $title, $data)
    {
        $chartjs = app()->chartjs
            ->name($name)
            ->type('bar')
            ->size(['width' => 800, 'height' => 500])
            ->labels($data['label'])
            ->datasets([
                [
                    "label" => "Total Count",
                    // 'borderDash' => [5, 5],
                    // 'pointRadius' => true,
                    'backgroundColor' => "rgba(255, 34, 21, 0.31)",
                    'borderColor' => "rgba(255, 34, 21, 0.7)",
                    "pointColor" => "rgba(255, 34, 21, 0.7)",
                    "pointStrokeColor" => "rgba(255, 34, 21, 0.7)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHighlightStroke" => "rgba(220,220,220,1)",
                    'data' => $data['dataTotalHour']
                ],
            ])
            ->options([ "type" => 'logarithmic']);

        $chartjs->optionsRaw([
            'title' => [
                'text' => $title,
                'display' => true,
                'position' => "top",
                'fontSize' => 18,
                'fontColor' => "#000"
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'legend' => [
                'position' => 'top',
            ],
            'scales' => [
                'xAxes' => [
                    [
                        'gridLines' => [
                            'display' => true
                        ]
                    ]
                ],
                'yAxes' => [
                    [
                        'gridLines' => [
                            'display' => false
                        ]
                    ]
                ],
            ]
        ]);

        return $chartjs;
    }
}
