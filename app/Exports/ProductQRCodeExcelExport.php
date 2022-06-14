<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;


use Maatwebsite\Excel\Concerns\FromCollection;

class ProductQRCodeExcelExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        return view('export.ProductQR-export', [
                    'data' => $this->data,
            ]);
    }
}



