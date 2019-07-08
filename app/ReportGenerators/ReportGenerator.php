<?php

declare(strict_types=1);

namespace App\ReportGenerators;

use App\Models\Report;

abstract class ReportGenerator
{
    /**
     * @var \App\Models\Report
     */
    protected $report;

    /**
     * ReportGenerator constructor.
     *
     * @param \App\Models\Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Generate the report and return the contents.
     *
     * @return string
     */
    abstract public function generate(): string;
}
