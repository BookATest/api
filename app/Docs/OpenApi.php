<?php

namespace App\Docs;

use App\Docs\Paths\{
    Appointments,
    Audits,
    Bookings,
    Clinics,
    EligibleAnswers,
    Questions,
    Reports,
    ReportSchedules,
    ServiceUsers,
    Settings,
    Stats,
    Users
};
use GoldSpecDigital\ObjectOrientedOAS\Objects\{
    Components,
    Contact,
    ExternalDocs,
    Info,
    PathItem,
    Paths,
    SecurityScheme,
    Server
};
use GoldSpecDigital\ObjectOrientedOAS\OpenApi as OpenApiSpec;

class OpenApi
{
    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\OpenApi
     */
    protected $openApi;

    /**
     * OpenApi constructor.
     */
    public function __construct()
    {
        $this->openApi = OpenApiSpec::create(
            $this->getVersion(),
            $this->getInfo(),
            $this->getPaths()
        );

        $this->openApi = $this->openApi
            ->servers(...$this->getServers())
            ->components($this->getComponents())
            ->security(...$this->getSecurity())
            ->tags(...Tags::all())
            ->externalDocs($this->getExternalDocs());
    }

    /**
     * @return array
     */
    public function generate(): array
    {
        return $this->openApi->toArray();
    }

    /**
     * @return string
     */
    protected function getVersion(): string
    {
        return OpenApiSpec::VERSION_3_0_1;
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Info
     */
    protected function getInfo(): Info
    {
        $appName = config('app.name');

        return Info::create("{$appName} API Specification", 'v1')
            ->description("For using the {$appName} API")
            ->contact($this->getContact());
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Contact
     */
    protected function getContact(): Contact
    {
        return Contact::create(
            'Ayup Digital',
            'https://ayup.agency',
            'info@ayup.agency'
        );
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Paths
     */
    protected function getPaths(): Paths
    {
        return Paths::create(
            PathItem::create('/appointments', Appointments::index(), Appointments::store()),
            PathItem::create('/appointments/{appointment}', Appointments::show(), Appointments::update(), Appointments::destroy()),
            PathItem::create('/appointments/{appointment}/cancel', Appointments::cancel()),
            PathItem::create('/appointments/{appointment}/schedule', Appointments::destroySchedule()),
            PathItem::create('/audits', Audits::index()),
            PathItem::create('/audits/{audit}', Audits::show()),
            PathItem::create('/bookings', Bookings::store()),
            PathItem::create('/bookings/eligible-clinics', Bookings::eligibleClinics()),
            PathItem::create('/clinics', Clinics::index(), Clinics::store()),
            PathItem::create('/clinics/{clinic}', Clinics::show(), Clinics::update(), Clinics::destroy()),
            PathItem::create('/clinics/{clinic}/eligible-answers', EligibleAnswers::index(), EligibleAnswers::update()),
            PathItem::create('/questions', Questions::index(), Questions::store()),
            PathItem::create('/reports', Reports::index(), Reports::store()),
            PathItem::create('/reports/{report}', Reports::show(), Reports::destroy()),
            PathItem::create('/reports/{report}/download', Reports::download()),
            PathItem::create('/report-schedules', ReportSchedules::index(), ReportSchedules::store()),
            PathItem::create('/report-schedules/{report_schedule}', ReportSchedules::show(), ReportSchedules::destroy()),
            PathItem::create('/service-users', ServiceUsers::index()),
            PathItem::create('/service-users/{service_user}', ServiceUsers::show()),
            PathItem::create('/service-users/{service_user}/appointments', ServiceUsers::appointments()),
            PathItem::create('/service-users/access-code', ServiceUsers::accessCode()),
            PathItem::create('/service-users/token', ServiceUsers::token()),
            PathItem::create('/service-users/token/{token}', ServiceUsers::showToken()),
            PathItem::create('/settings', Settings::index(), Settings::update()),
            PathItem::create('/stats', Stats::index()),
            PathItem::create('/users', Users::index(), Users::store()),
            PathItem::create('/users/{user}', Users::show(), Users::update(), Users::destroy()),
            PathItem::create('/users/{user}/profile-picture.png', Users::profilePicture()),
            PathItem::create('/users/{user}/calendar-feed-token', Users::calendarFeedToken())
        );
    }

    /**
     * @return array
     */
    protected function getServers(): array
    {
        $appUrl = config('app.url');

        return [Server::create("{$appUrl}/v1/")];
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Components
     */
    protected function getComponents(): Components
    {
        $passwordFlow = [
            'tokenUrl' => url('/oauth/token'),
        ];

        return Components::create()
            ->securitySchemes(
                SecurityScheme::oauth2('OAuth2', ['password' => $passwordFlow])
            );
    }

    /**
     * @return array
     */
    protected function getSecurity(): array
    {
        return [
            ['OAuth2' => []],
        ];
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs
     */
    protected function getExternalDocs(): ExternalDocs
    {
        return ExternalDocs::create('https://github.com/BookATest/api/wiki')
            ->description('GitHub Wiki');
    }
}
