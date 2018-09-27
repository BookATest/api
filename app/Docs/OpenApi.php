<?php

namespace App\Docs;

use App\Docs\Paths\Appointments;
use App\Docs\Paths\Audits;
use App\Docs\Paths\Bookings;
use App\Docs\Paths\Clinics;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Components;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Contact;
use GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Paths;
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
            PathItem::create('/bookings', Bookings::store()),
            PathItem::create('/bookings/eligible-clinics', Bookings::eligibleClinics()),
            PathItem::create('/clinics', Clinics::index(), Clinics::store()),
            PathItem::create('/clinics/{clinic}', Clinics::show(), Clinics::update())
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
            'tokenUrl' => url('/oauth/token')
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
            ['OAuth2' => []]
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
