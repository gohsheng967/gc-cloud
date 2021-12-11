<?php

namespace App\Http\Controllers;

use App\Models\ProductReport;
use App\Models\History;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class HomeController extends Controller
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
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get all data for summary
        $userCount = User::count();
        $reportToday = ProductReport::whereBetween('scan_time', [Carbon::now()->format('Y-m-d 00:00:00'),Carbon::now()->format('Y-m-d 23:59:59')] )->count();
        $qrCodeCount = Product::count();

        return view('home', compact('userCount', 'reportToday', 'qrCodeCount'));
    }
}
