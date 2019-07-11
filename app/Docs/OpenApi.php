<?php

namespace App\Docs;

use App\Docs\Paths\Appointments;
use App\Docs\Paths\Audits;
use App\Docs\Paths\Bookings;
use App\Docs\Paths\Clinics;
use App\Docs\Paths\EligibleAnswers;
use App\Docs\Paths\Questions;
use App\Docs\Paths\Reports;
use App\Docs\Paths\ReportSchedules;
use App\Docs\Paths\ServiceUsers;
use App\Docs\Paths\Settings;
use App\Docs\Paths\Stats;
use App\Docs\Paths\Users;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Components;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Contact;
use GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OAuthFlow;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement;
use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityScheme;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Server;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi as OpenApiSpec;

class OpenApi
{
    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\OpenApi
     */
    protected $openApi;

    /**
     * OpenApi constructor.
     *
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     */
    public function __construct()
    {
        $this->openApi = OpenApiSpec::create()
            ->openapi($this->getVersion())
            ->info($this->getInfo())
            ->paths(...$this->getPaths())
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
        return OpenApiSpec::OPENAPI_3_0_2;
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Info
     */
    protected function getInfo(): Info
    {
        $appName = config('app.name');

        return Info::create()
            ->title("{$appName} API Specification")
            ->version('v1')
            ->description("For using the {$appName} API")
            ->contact($this->getContact());
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Contact
     */
    protected function getContact(): Contact
    {
        return Contact::create()
            ->name('Ayup Digital')
            ->url('https://ayup.agency')
            ->email('info@ayup.agency');
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem[]
     */
    protected function getPaths(): array
    {
        return [
            PathItem::create()
                ->route('/appointments')
                ->operations(Appointments::index(), Appointments::store()),
            PathItem::create()
                ->route('/appointments.ics')
                ->operations(Appointments::indexIcs()),
            PathItem::create()
                ->route('/appointments/{appointment}')
                ->operations(Appointments::show(), Appointments::update(), Appointments::destroy()),
            PathItem::create()
                ->route('/appointments/{appointment}/cancel')
                ->operations(Appointments::cancel()),
            PathItem::create()
                ->route('/appointments/{appointment}/schedule')
                ->operations(Appointments::destroySchedule()),
            PathItem::create()
                ->route('/audits')
                ->operations(Audits::index()),
            PathItem::create()
                ->route('/audits/{audit}')
                ->operations(Audits::show()),
            PathItem::create()
                ->route('/bookings')
                ->operations(Bookings::store()),
            PathItem::create()
                ->route('/bookings/eligibility')
                ->operations(Bookings::eligibleClinics()),
            PathItem::create()
                ->route('/clinics')
                ->operations(Clinics::index(), Clinics::store()),
            PathItem::create()
                ->route('/clinics/{clinic}')
                ->operations(Clinics::show(), Clinics::update(), Clinics::destroy()),
            PathItem::create()
                ->route('/clinics/{clinic}/eligible-answers')
                ->operations(EligibleAnswers::index(), EligibleAnswers::update()),
            PathItem::create()
                ->route('/questions')
                ->operations(Questions::index(), Questions::store()),
            PathItem::create()
                ->route('/reports')
                ->operations(Reports::index(), Reports::store()),
            PathItem::create()
                ->route('/reports/{report}')
                ->operations(Reports::show(), Reports::destroy()),
            PathItem::create()
                ->route('/reports/{report}/download')
                ->operations(Reports::download()),
            PathItem::create()
                ->route('/report-schedules')
                ->operations(ReportSchedules::index(), ReportSchedules::store()),
            PathItem::create()
                ->route('/report-schedules/{report_schedule}')
                ->operations(ReportSchedules::show(), ReportSchedules::destroy()),
            PathItem::create()
                ->route('/service-users')
                ->operations(ServiceUsers::index()),
            PathItem::create()
                ->route('/service-users/{service_user}')
                ->operations(ServiceUsers::show()),
            PathItem::create()
                ->route('/service-users/{service_user}/appointments')
                ->operations(ServiceUsers::appointments()),
            PathItem::create()
                ->route('/service-users/access-code')
                ->operations(ServiceUsers::accessCode()),
            PathItem::create()
                ->route('/service-users/token')
                ->operations(ServiceUsers::token()),
            PathItem::create()
                ->route('/service-users/token/{token}')
                ->operations(ServiceUsers::showToken()),
            PathItem::create()
                ->route('/settings')
                ->operations(Settings::index(), Settings::update()),
            PathItem::create()
                ->route('/settings/logo.png')
                ->operations(Settings::logo()),
            PathItem::create()
                ->route('/settings/styles.css')
                ->operations(Settings::styles()),
            PathItem::create()
                ->route('/stats')
                ->operations(Stats::index()),
            PathItem::create()
                ->route('/users')
                ->operations(Users::index(), Users::store()),
            PathItem::create()
                ->route('/users/user')
                ->operations(Users::user()),
            PathItem::create()
                ->route('/users/user/sessions')
                ->operations(Users::destroySessions()),
            PathItem::create()
                ->route('/users/{user}')
                ->operations(Users::show(), Users::update(), Users::destroy()),
            PathItem::create()
                ->route('/users/{user}/profile-picture.jpg')
                ->operations(Users::profilePicture()),
            PathItem::create()
                ->route('/users/{user}/calendar-feed-token')
                ->operations(Users::calendarFeedToken()),
        ];
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Server[]
     */
    protected function getServers(): array
    {
        $appUrl = config('app.url');

        return [
            Server::create()->url("{$appUrl}/v1/"),
        ];
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Components
     */
    protected function getComponents(): Components
    {
        $passwordFlow = OAuthFlow::create()
            ->flow(OAuthFlow::FLOW_PASSWORD)
            ->tokenUrl(url('/oauth/token'));

        return Components::create()
            ->securitySchemes(
                SecurityScheme::oauth2('OAuth2')->flows($passwordFlow)
            );
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement[]
     */
    protected function getSecurity(): array
    {
        return [
            SecurityRequirement::create()->securityScheme('Oauth2'),
        ];
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs
     */
    protected function getExternalDocs(): ExternalDocs
    {
        return ExternalDocs::create()
            ->url('https://github.com/BookATest/api/wiki')
            ->description('GitHub Wiki');
    }
}
