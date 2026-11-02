<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        // Defaults to the last 30 days.
        $request->mergeIfMissing([
            'start_date' => Carbon::now()->subDays(29)->toDateString(),
            'end_date'   => Carbon::now()->toDateString(),
        ]);

        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $stats = $this->reportService->getSummaryStats($startDate, $endDate);
        $revenueTrend = $this->reportService->getDailyRevenueTrend($startDate, $endDate);
        $topItems = $this->reportService->getTopSellingItems($startDate, $endDate);
        $paymentSplit = $this->reportService->getPaymentMethodSplit($startDate, $endDate);

        return view('admin.reports.index', compact(
            'stats',
            'revenueTrend',
            'topItems',
            'paymentSplit',
            'startDate',
            'endDate'
        ));
    }
}
