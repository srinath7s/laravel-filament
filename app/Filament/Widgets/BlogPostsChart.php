<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class BlogPostsChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'blogPostsChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'BlogPostsChart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'BasicBarChart',
                    'data' => [7, 10, 13, 15, 18],
                ],
            ],
            'xaxis' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => true,
                ],
            ],
        ];
    }
}
