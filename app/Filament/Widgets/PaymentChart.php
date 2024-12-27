<?php
namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class PaymentChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Generated (2 Years)';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public function getPaymentsData(int $currentYear, int $previousYear): array
    {
        // Fetch payments filtered by the current and previous years and status 'paid'
        $paymentsCompletedCurrentYear = Payment::where('status', 'paid')
            ->whereYear('created_at', $currentYear)
            ->get();

        $paymentsCompletedPreviousYear = Payment::where('status', 'paid')
            ->whereYear('created_at', $previousYear)
            ->get();

        $monthlyRevenueCurrentYear = array_fill(0, 12, 0);
        $monthlyRevenuePreviousYear = array_fill(0, 12, 0);


        foreach ($paymentsCompletedCurrentYear as $payment) {
            $month = Carbon::parse($payment->created_at)->month - 1;
            $monthlyRevenueCurrentYear[$month] += $payment->amount;
        }

        foreach ($paymentsCompletedPreviousYear as $payment) {
            $month = Carbon::parse($payment->created_at)->month - 1;
            $monthlyRevenuePreviousYear[$month] += $payment->amount;
        }

        return [
            'currentYear' => $monthlyRevenueCurrentYear,
            'previousYear' => $monthlyRevenuePreviousYear,
        ];
    }


    protected function getData(): array
    {
        $currentYear = Carbon::now()->year; // Use the current year
        $previousYear = $currentYear - 1; // Get the previous year
        $paymentData = $this->getPaymentsData($currentYear, $previousYear);

        return [
            'datasets' => [
                [
                    'label' => "Revenue Generated ($currentYear)",
                    'data' => $paymentData['currentYear'],
                    'fill' => false, // Do not fill the area under the line
                    'borderColor' => '#36A2EB', //(Blue)
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#36A2EB', //(Blue)
                    'pointBorderColor' => '#FFFFFF',//(White)
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => "Revenue Generated ($previousYear)",
                    'data' => $paymentData['previousYear'],
                    'fill' => false,
                    'borderColor' => '#FF6384', //(Red)
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#FF6384', // (Red)
                    'pointBorderColor' => '#FFFFFF', //  (White)
                    'pointBorderWidth' => 2,
                ],
            ],
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ], // Months of the year
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Use 'line' for a line chart
    }

    protected function getStyles(): array
    {
        return [
            'chart' => [
                'width' => '100%',
                'max-width' => '100%',
                'height' => '400px',
            ],
        ];
    }
}
