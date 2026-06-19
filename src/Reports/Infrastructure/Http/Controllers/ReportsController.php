<?php

namespace Src\Reports\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Src\Reports\Application\Services\ReportsService;

class ReportsController extends Controller
{
    public function __construct(private ReportsService $service) {}

    public function index()
    {
        return view('Reports.index');
    }

    public function transactions(Request $request)
    {
        $filters = $request->only(['member_oid', 'campaign_oid', 'type', 'date_from', 'date_to']);

        try {
            $transactions = $this->service->transactionReport($filters);
            $members      = $this->service->membersDropdown();
            $campaigns    = $this->service->campaignsDropdown();
        } catch (\Throwable $e) {
            $transactions = [];
            $members      = [];
            $campaigns    = [];
            $loadError    = $e->getMessage();
        }

        return view('Reports.transactions', array_merge(
            compact('transactions', 'members', 'campaigns', 'filters'),
            isset($loadError) ? compact('loadError') : [],
        ));
    }
}
