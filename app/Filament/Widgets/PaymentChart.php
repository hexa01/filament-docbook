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

    /**
     * Determine if the widget should be visible.
     *
     * @return bool
     */
    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get the payment data for completed (paid) status in the specified year.
     *
     * @return array
     */
    public function getPaymentsData(int $currentYear, int $previousYear): array
    {
        // Fetch payments filtered by the current and previous years and status 'paid'
        $paymentsCompletedCurrentYear = Payment::where('status', 'paid')
            ->whereYear('created_at', $currentYear)
            ->get();

        $paymentsCompletedPreviousYear = Payment::where('status', 'paid')
            ->whereYear('created_at', $previousYear)
            ->get();

        // Initialize arrays to store the total amount for each month for both years
        $monthlyRevenueCurrentYear = array_fill(0, 12, 0); // For current year (12 months)
        $monthlyRevenuePreviousYear = array_fill(0, 12, 0); // For previous year (12 months)

        // Calculate the total amount for completed (paid) payments per month for the current year
        foreach ($paymentsCompletedCurrentYear as $payment) {
            $month = Carbon::parse($payment->created_at)->month - 1; // Get the month index (0-11)
            $monthlyRevenueCurrentYear[$month] += $payment->amount;
        }

        // Calculate the total amount for completed (paid) payments per month for the previous year
        foreach ($paymentsCompletedPreviousYear as $payment) {
            $month = Carbon::parse($payment->created_at)->month - 1; // Get the month index (0-11)
            $monthlyRevenuePreviousYear[$month] += $payment->amount;
        }

        return [
            'currentYear' => $monthlyRevenueCurrentYear,
            'previousYear' => $monthlyRevenuePreviousYear,
        ];
    }

    /**
     * Prepare the data for the chart widget.
     *
     * @return array
     */
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
                    'borderColor' => '#36A2EB', // Line color for the current year (Blue)
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#36A2EB', // Point color for the current year (Blue)
                    'pointBorderColor' => '#FFFFFF', // Point border color for the current year (White)
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => "Revenue Generated ($previousYear)",
                    'data' => $paymentData['previousYear'],
                    'fill' => false, // Do not fill the area under the line
                    'borderColor' => '#FF6384', // Line color for the previous year (Red)
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#FF6384', // Point color for the previous year (Red)
                    'pointBorderColor' => '#FFFFFF', // Point border color for the previous year (White)
                    'pointBorderWidth' => 2,
                ],
            ],
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ], // Months of the year
        ];
    }

    /**
     * Define the type of the chart (line chart in this case).
     *
     * @return string
     */
    protected function getType(): string
    {
        return 'line'; // Use 'line' for a line chart
    }

    /**
     * Additional styling for the chart.
     *
     * @return array
     */
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
