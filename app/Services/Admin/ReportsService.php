<?php

namespace App\Services\Admin;

use App\Services\BaseService;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use App\Models\GeneratedReport;
use App\Models\InsuranceProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReportsService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = GeneratedReport::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }

    // Define allowed filters
    protected function getAllowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new GlobalSearchFilter, 'name', 'email'),
            'name',
            'email',
            AllowedFilter::exact('status'),
        ];
    }

    // Define allowed includes relationships
    protected function getAllowedIncludes(): array
    {
        return [
            //
        ];
    }

    // Define allowed sorts
    protected function getAllowedSorts(): array
    {
        return [
            'id',
            'name',
            'created_at',
        ];
    }


    //generate report
    public function generateReport(array $data): array
    {
        $type = $data['type'];
        $format = $data['format'];
        $reportName = $data['report_name'];
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;

        // Handle single-day report generation
        if ($startDate && !$endDate) {
            $endDate = $startDate;
        }

        $filename = Str::slug($reportName) . '-' . time() . '.' . $format;
        $filePath = 'reports/' . $filename;

        $data = [];
        $columns = [];
        if ($type === 'user') {
            $columns = ['ID', 'First Name', 'Last Name', 'Email', 'Joined At'];
            // Start the query
            $query = User::select('id', 'first_name', 'last_name', 'email', 'created_at');

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            }

            $data = $query->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'created_at' => $user->created_at->format('d/m/Y'),
                    ];
                })
                ->toArray();
        } elseif ($type === 'provider') {
            $columns = ['ID', 'Provider Name', 'Status', 'Review Count', 'Created At'];
            // Start the query
            $query = InsuranceProvider::select('id', 'name', 'status', 'review_count', 'created_at');

            // Conditionally add the date range filter
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            }

            $data = $query->get()
                ->map(function ($provider) {
                    // Return a new array instead of modifying the model
                    return [
                        'id' => $provider->id,
                        'name' => $provider->name,
                        'status' => $provider->status,
                        'review_count' => $provider->review_count,
                        'created_at' => $provider->created_at->format('d/m/Y'),
                    ];
                })
                ->toArray();
        } elseif ($type === 'review') {
            $columns = ['Review ID', 'User Name', 'Provider Name', 'Rating', 'Status', 'Submitted At'];
            // Start the query
            $query = DB::table('reviews')
                ->join('users', 'reviews.user_id', '=', 'users.id')
                ->join('insurance_providers', 'reviews.provider_id', '=', 'insurance_providers.id')
                ->select(
                    'reviews.id',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user_name"),
                    'insurance_providers.name as provider_name',
                    'reviews.overall_rating',
                    'reviews.status',
                    'reviews.submitted_at'
                );

            // Conditionally add the date range filter
            if ($startDate && $endDate) {
                //Filter by the correct submission date column
                $query->whereBetween('reviews.submitted_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            }

            $data = $query->get()
                ->map(function ($review) {
                    // Parse and format the correct submission date column
                    $review->submitted_at = Carbon::parse($review->submitted_at)->format('d/m/Y');
                    return (array)$review; // Convert object to array for CSV
                })
                ->toArray();
        }

        // Check if data is empty and return a message
        if (empty($data)) {
            throw new \Exception('No data found for the selected criteria. Report cannot be generated.');
        }

        if ($format === 'pdf') {
            $viewData = [
                'title' => $reportName,
                'date' => date('d/m/Y'), // Also format the report generation date
                'columns' => $columns,
                'data' => $data,
            ];
            $pdf = Pdf::loadView('reports.template', $viewData);
            Storage::disk('public')->put($filePath, $pdf->output());
        } elseif ($format === 'csv') {
            $file = fopen('php://temp', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                // Ensure row is an array for fputcsv
                fputcsv($file, (array)$row);
            }
            rewind($file);
            Storage::disk('public')->put($filePath, stream_get_contents($file));
            fclose($file);
        }

        $report = GeneratedReport::create([
            'report_name' => $reportName,
            'type' => $type,
            'format' => $format,
            'file_path' => $filePath,
            'generated_by' => Auth::id() ?? 1,
        ]);

        $data = [
            'report' => $report,
            'download_url' => Storage::disk('public')->url($filePath)
        ];
        return $data;
    }


     public function getUserGrowth(string $period): array
    {
        if ($period === 'monthly') {
            $startDate = now()->subMonths(11)->startOfMonth();
            $endDate = now()->endOfMonth();
            $users = DB::table('users')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw('DATE_FORMAT(created_at, "%b") as month'), DB::raw('count(id) as count'))
                ->groupBy('month')
                ->orderByRaw('MIN(created_at)')
                ->pluck('count', 'month')
                ->all();
            $labels = [];
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $monthName = $month->format('M');
                $labels[] = $monthName;
                $data[] = $users[$monthName] ?? 0;
            }
        } else {
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
            $users = DB::table('users')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw('DATE_FORMAT(created_at, "%a") as day'), DB::raw('count(id) as count'))
                ->groupBy('day')
                ->orderByRaw('MIN(created_at)')
                ->pluck('count', 'day')
                ->all();
            $labels = [];
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $dayName = $day->format('D');
                $labels[] = $dayName;
                $data[] = $users[$dayName] ?? 0;
            }
        }
        return ['labels' => $labels, 'data' => $data];
    }

    public function getTopProvidersByReviews(string $period): array
    {
        $days = ($period === 'monthly') ? 30 : 7;
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();
        $topProviders = DB::table('reviews')
            ->join('insurance_providers', 'reviews.provider_id', '=', 'insurance_providers.id')
            // Filter by submitted_at for consistency
            ->whereBetween('reviews.submitted_at', [$startDate, $endDate])
            ->select('insurance_providers.name', DB::raw('count(reviews.id) as review_count'))
            ->groupBy('insurance_providers.name')
            ->orderBy('review_count', 'desc')
            ->limit(6)
            ->pluck('review_count', 'name')
            ->all();
        return ['labels' => array_keys($topProviders), 'data' => array_values($topProviders)];
    }
}
