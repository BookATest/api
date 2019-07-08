<?php

namespace App\ReportGenerators;

use App\Exceptions\InvalidReportTypeException;
use App\Models\Report;

class ReportGeneratorFactory
{
    /**
     * ReportGeneratorFactory constructor.
     */
    protected function __construct()
    {
        // Prevent instantiation.
    }

    /**
     * @param \App\Models\Report $report
     * @return \App\ReportGenerators\ReportGenerator
     * @throws \App\Exceptions\InvalidReportTypeException
     */
    public static function for(Report $report): ReportGenerator
    {
        $reportType = $report->reportType->name;
        $reportType = studly_case($reportType);
        $className = __NAMESPACE__ . '\\' . $reportType . 'Generator';

        if (class_exists($className)) {
            return app($className, [$report]);
        }

        throw new InvalidReportTypeException("Class does not exist [$className]");
    }
}
