<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\History;
use App\Models\ProductReport;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Batch;
use Illuminate\Http\Request;
use Response;
use Carbon\Carbon;
use Config;
use File;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\URL;
use Log;

class ApiReportController extends Controller
{
    /**
     * Store data report to DB
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
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
        // $userId = $new['user_id'];

        if (!empty($key)) {
            if ($key == $getSetting->key_app) {

                // Check-in
                if ($q) {

                    // Get data from request
                    $in_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d H:i:s'));


                    // Save the data
                    $save = new ProductReport();
                    // $save->user_id = $userId;
                    $save->scan_time = $in_time;
                    $save->scan_product_id = $qrId;

                    $createNew = $save->save();

                    $product = Product::select('sku.sku_code','sku.sku_category', 'sku.image', 'sku.source_from', 'halal_cert_no',
                                                'sku.manufacturer', 'sku.temperature','batch.batch_no','batch.expired_date', 'product.serial_no')
                                ->leftjoin('sku', 'sku.id', 'product.sku_id')
                                ->leftjoin('batch', 'batch.id', 'product.batch_id')
                                ->where('product.id', $qrId)->first();

                    $baseURL = url("uploads/".$product->image);
                    // Saving
                    if ($createNew) {
                        $data = [
                            'message' => 'Success!',
                            'date' => Carbon::parse($date)->format('Y-m-d'),
                            'time' => Carbon::parse($in_time)->format('H:i:s'),
                            // 'location' => $location,
                            'skucode' => $product->sku_code,
                            'skucategory' => $product->sku_category,
                            'sourcefrom' => $product->source_from,
                            'manufacturer' => $product->manufacturer,
                            'temperature' => $product->temperature,
                            'batchno' => $product->batch_no,
                            'expireddate' => Carbon::parse($product->expired_date)->format('Y-m-d'),
                            'serialno' => $product->serial_no,
                            'image' => $baseURL,
                            'halalcert' => (empty($product->halal_cert_no)? '-':$product->halal_cert_no),

                        ];
                        // log::debug($data);
                        return response()->json($data, 200);
                    }

                    $data = [
                        'message' => 'Error! Something Went Wrong!',
                    ];
                    return response()->json($data, 200);
                }

                $data = [
                    'message' => 'Error! Wrong Command!',
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
