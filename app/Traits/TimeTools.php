<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait TimeTools
{
    protected bool $formatMetric = false;

    /**
     * Format the metric values
     *
     * @return $this
     */
    protected function fmt(?bool $formatMetric = false): self
    {
        $this->formatMetric = !!$formatMetric;
        return $this;
    }

    /**
     * Get the the time based on provided period
     *
     * @param  string<'today'|'yesterday'|'week'|'month'|'year'>  $timeframe
     * @param  bool  $useRange
     * @param  ?Carbon  $base
     * @return ($useRange is true ? Carbon[] : Carbon)
     */
    private function getStartDate(?string $timeframe, $useRange = false, ?Carbon $base = null): Carbon|array
    {
        $base ??= now();

        $range = match ($timeframe) {
            'today' => [$base->clone()->startOfDay(), $base->clone()->endOfDay()],
            'yesterday' => [$base->clone()->subDay()->startOfDay(), $base->clone()->subDay()->endOfDay()],
            'week' => [$base->clone()->startOfWeek(), $base->clone()->endOfWeek()],
            'month' => [$base->clone()->startOfMonth(), $base->clone()->endOfMonth()],
            'year' => [$base->clone()->startOfYear(), $base->clone()->endOfYear()],
            default => [$base->clone()->startOfYear(), $base->clone()->endOfYear()],
        };

        if (!$useRange) {
            return match ($timeframe) {
                'today' => $range[0],
                'yesterday' => $range[0],
                'week' => $range[0],
                'month' => $range[0],
                'year' => $range[0],
                default => $$range[0],
            };
        }

        return $range;
    }

    /**
     * Get the name of the callable trend method for the timeframe
     *
     * @param  string<'today'|'yesterday'|'week'|'month'|'year'>  $timeframe
     * @return  string<'perHour'|'perDay'|'perMonth'>
     */
    private function getTrendMethod(string $timeframe): string
    {
        return match ($timeframe) {
            'today' => 'perHour',
            'yesterday' => 'perHour',
            'week' => 'perDay',
            'month' => 'perDay',
            'year' => 'perMonth',
            default => 'perDay',
        };
    }

    /**
     * Get the format for the timeframe label key
     *
     * @param  string<'today'|'yesterday'|'week'|'month'|'year'>  $timeframe
     * @return  string<'HH'|'ddd'|'DD'|'MMM'>
     */
    private function getFormat(string $timeframe): string
    {
        return match ($timeframe) {
            'today' => 'hA',
            'yesterday' => 'hA',
            'week' => 'ddd',
            'month' => 'MMM Do',
            'year' => 'MMM',
            default => 'DD',
        };
    }

    /**
     * Transform data for Chart.js
     *
     * @param Collection $results
     * @param string $dataKey
     * @param string $countKey
     * @param array{type:string,cols:int} $config
     * @return array
     */
    public function formatForChartJs(Collection $results, string $dataKey, $countKey = 'count', array $config = []): array
    {
        return [
            ...$config,
            "labels" => $results->pluck($dataKey)->map(fn ($val) => match ($val) {
                null => 'Unknown',
                "0" => ucwords("Not $dataKey"),
                "1" => ucwords($dataKey),
                default => str($val)->replace(['-', '_'], ' ')->apa()->toString(),
            })->toArray(),
            "datasets" => [
                [
                    "label" => str($dataKey)->replace(['-', '_'], ' ')->apa()->toString(),
                    "data" => $results->pluck($countKey)->toArray(),
                    "backgroundColor" => $results->map(fn() => $this->generateRandomHexColor())->toArray()
                ]
            ]
        ];
    }

    private function getTimeDiffMetric(
        Builder $query,
        $aggr = 'sum',
        $field = 'amount',
        $format = false,
        ?Carbon $base = null
    ) {
        $base ??= now();
        $format = $this->formatMetric || $format;

        $week_time = $this->getStartDate('week', true, $base);
        $month_time = $this->getStartDate('month', true, $base);
        $today_time = $this->getStartDate('today', true, $base);

        $last_week_time = $this->getStartDate('week', true, $base->subWeek());
        $last_month_time = $this->getStartDate('month', true, $base->subMonth());
        $previous_day_time = $this->getStartDate('yesterday', true, $base);

        // Revenue calculations
        $total = (clone $query)->{$aggr}($field);
        $daily = (clone $query)->whereBetween('created_at', $today_time)->{$aggr}($field);
        $weekly = (clone $query)->whereBetween('created_at', $week_time)->{$aggr}($field);
        $monthly = (clone $query)->whereBetween('created_at', $month_time)->{$aggr}($field);

        $previous_day = (clone $query)->whereBetween('created_at', $previous_day_time)->{$aggr}($field);
        $last_weekly = (clone $query)->whereBetween('created_at', $last_week_time)->{$aggr}($field);
        $last_monthly = (clone $query)->whereBetween('created_at', $last_month_time)->{$aggr}($field);

        // Increase compared to previous day
        $daily_change = $previous_day > 0
            ? ($daily - $previous_day)
            : $daily;

        // Percentage increase compared to previous day
        $daily_change_percentage = $previous_day > 0
            ? (($daily - $previous_day) / $previous_day) * 100
            : ($daily > 0 ? 100 : 0);

        // Increase compared to last week
        $weekly_change = $last_weekly > 0
            ? ($weekly - $last_weekly)
            : $weekly;

        // Percentage increase this week
        $weekly_change_percentage = $last_weekly > 0
            ? (($weekly - $last_weekly) / $last_weekly) * 100
            : ($weekly > 0 ? 100 : 0);

        // Increase compared to last month
        $monthly_change = max(0, $last_monthly > 0
            ? ($monthly - $last_monthly)
            : $monthly);

        // Percentage increase this month
        $monthly_change_percentage = $last_monthly > 0
            ? (($monthly - $last_monthly) / $last_monthly) * 100
            : ($monthly > 0 ? 100 : 0);

        // Formatting values for display
        return [
            'total' => $format ? number_format($total, 2) : round($total, 2),
            'daily' => $format ? number_format($daily, 2) : round($daily, 2),
            'weekly' => $format ? number_format($weekly, 2) : round($weekly, 2),
            'monthly' => $format ? number_format($monthly, 2) : round($monthly, 2),
            'previous_day' => $format ? number_format($previous_day, 2) : round($previous_day, 2),
            'daily_change' => $format ? number_format($daily_change, 2) : round($daily_change, 2),
            'weekly_change' => $format ? number_format($weekly_change, 2) : round($weekly_change, 2),
            'monthly_change' => $format ? number_format($monthly_change, 2) : round($monthly_change, 2),
            'daily_change_percentage' => round($daily_change_percentage, 2),
            'weekly_change_percentage' => round($weekly_change_percentage, 2),
            'monthly_change_percentage' => round($monthly_change_percentage, 2),
        ];
    }

    private function generateRandomHexColor()
    {
        $characters = '0123456789ABCDEF';
        $color = '#';
        for ($i = 0; $i < 6; $i++) {
            $color .= $characters[rand(0, 15)];
        }
        return $color;
    }

}
