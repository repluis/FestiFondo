<?php

namespace Src\FundTransfers\Infrastructure\Http\Controllers;

use Illuminate\Routing\Controller;

class FundTransferController extends Controller
{
    public function index()
    {
        return view('FundTransfers.index');
    }
}

