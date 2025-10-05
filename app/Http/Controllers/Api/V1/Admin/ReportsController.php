<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportRequest;
use App\Models\GeneratedReport;
use App\Services\Admin\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ReportsController extends Controller
{
    protected ReportsService $reportsService;
    public function __construct(ReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
    }

    //get chart data
    public function getChartData(Request $request)
    {
        $period = $request->query('period', 'weekly'); // 'weekly' or 'monthly'

        $userGrowth = $this->reportsService->getUserGrowth($period);
        $topProviders = $this->reportsService->getTopProvidersByReviews($period);

        $data = [
            'user_growth' => $userGrowth,
            'top_providers' => $topProviders,
        ];
        return response_success('Chart data retrieved successfully.', $data);
    }

    //get recent reports
    public function getRecentReports()
    {
        $reports = $this->reportsService->getAll();
        if ($reports->isEmpty()) {
            return response_error('No reports found.', [], 404);
        }
        return response_success('Recent reports retrieved successfully.', $reports);
    }



    //* Generate report
    public function generateReport(ReportRequest $request)
    {
        $validated = $request->validated();
        $data = $this->reportsService->generateReport($validated);
        return response_success('Report generated successfully.', $data);
    }

    public function downloadReport($id)
    {
        $report = GeneratedReport::findOrFail($id);
        return Storage::disk('public')->download($report->file_path);
    }

}

