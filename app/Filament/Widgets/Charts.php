<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class Charts extends ChartWidget
{
    protected static ?string $heading = 'Bar Chart - Completed and Missed Appointments';
    // protected static ?int $sort = 2;

    protected static ?int $sort = 1;
    // protected int|string|array $columnSpan = '1'; // Adjust column span as needed

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
     * Get the appointment data for this year and last year.
     *
     * @return array
     */
    public function getAppointmentsData(): array
    {
        // $currentYear = Carbon::now()->year;
        // $year = $year ?? Carbon::now()->year;

        // Fetch the number of appointments for each month in the current year
        $completedAppointments = Appointment::selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
        ->where('status','completed')
            ->groupBy('date')
            ->orderBy('date')
            ->take(7)
            ->pluck('count', 'date');

        $missedAppointments = Appointment::selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
        ->where('status','missed')
            ->groupBy('date')
            ->orderBy('date')
            ->take(7)
            ->pluck('count', 'date');

            return [
                'completedAppointments' => $completedAppointments,
                'missedAppointments' => $missedAppointments,
            ];
    }
    /**
     * Prepare data for the chart widget.
     *
     * @return array
     */
    protected function getData(): array
    {
        // Get completed appointments grouped by month
        $completedAppointments = Appointment::selectRaw('strftime("%Y-%m", appointment_date) as month, COUNT(*) as count')
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Get missed appointments grouped by month
        $missedAppointments = Appointment::selectRaw('strftime("%Y-%m", appointment_date) as month, COUNT(*) as count')
            ->where('status', 'missed')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Merge and ensure both datasets have the same months
        $allMonths = $completedAppointments->keys()->merge($missedAppointments->keys())->unique()->sort();

        $completedData = $allMonths->map(fn($month) => $completedAppointments->get($month, 0))->toArray();
        $missedData = $allMonths->map(fn($month) => $missedAppointments->get($month, 0))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Completed Appointments',
                    'data' => $completedData,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Missed Appointments',
                    'data' => $missedData,
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FFB1C1',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $allMonths->map(fn($month) => date('F Y', strtotime($month)))->toArray(), // Month labels (e.g., "January 2024")
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
