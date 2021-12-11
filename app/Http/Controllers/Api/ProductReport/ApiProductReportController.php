<?php

namespace App\Http\Controllers\Api\ProductReport;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use App\Models\History;
use App\Models\Setting;
use App\Models\Product;
use Illuminate\Http\Request;
use Response;
use Carbon\Carbon;
use Config;
use File;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class ApiProductReportController extends Controller
{
    public function apiSaveReport(Request $request)
    {
        // Get all request
        $new = $request->all();

        // Get data setting
        $getSetting = Setting::find(1);

        // Get data from request
        $key = $new['key'];
        $q = $new['q'];
        try {
            $qrId = Crypt::decryptString($new['qr_id']);
        } catch (DecryptException $e) {
            return 'Error Qr!';
        }
        $date = Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d');
        $userId = $new['user_id'];
        $product = Product::find($qrId)->ladang_ternakan;

        if (!empty($key)) {
            if ($key == $getSetting->key_app) {

                // Check-in

                // Get data from request
                $scan_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));
                    
                // Save the data
                $save = new ProductReport();
                $save->user_id = $userId;
                $save->scan_time = $scan_time;
                $save->scan_product_id = $qrId;
                $createNew = $save->save();

                    // Saving
                    if ($createNew) {
                        $data = [
                            'message' => 'Success!',
                            'date' => Carbon::parse($date)->format('Y-m-d'),
                            'time' => Carbon::parse($in_time)->format('H:i:s'),
                            'product' => $product,
                            'query' => 'Check-in',
                        ];
                        return response()->json($data, 200);
                    }

                    $data = [
                        'message' => 'Error! Something Went Wrong!',
                    ];
                    return response()->json($data, 200);
                
            }
            $data = [
                'message' => 'The KEY is Wrong!',
            ];
            return response()->json($data, 200);
        }
        $data = [
            'message' => 'Please Setting KEY First!',
        ];
        return response()->json($data, 200);
    }
}
