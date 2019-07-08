<?php

declare(strict_types=1);

namespace App\Notifications\Email\CommunityWorker;

use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportSchedule;
use App\Notifications\Email\Email;

class ReportGeneratedEmail extends Email
{
    /**
     * ReportGeneratedEmail constructor.
     *
     * @param \App\Models\Report $report
     * @param \App\Models\ReportSchedule $reportSchedule
     */
    public function __construct(Report $report, ReportSchedule $reportSchedule)
    {
        parent::__construct();

        $reportType = str_replace('_', ' ', $report->reportType->name);

        $this->to = $report->user->email;
        $this->subject = sprintf('%s %s Report Generated', ucwords($reportSchedule->repeat_type), ucwords($reportType));
        $this->message = "Your {$reportSchedule->repeat_type} {$reportType} report has been generated. Login to the admin portal to download it.";
        $this->notification = $report->user->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $report->user->email,
            'message' => $this->message,
        ]);
    }
}
