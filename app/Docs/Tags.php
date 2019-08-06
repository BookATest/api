<?php

namespace App\Docs;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;

class Tags
{
    /**
     * Tags constructor.
     */
    protected function __construct()
    {
        // Prevent instantiation.
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag[]
     */
    public static function all(): array
    {
        return [
            static::appointments(),
            static::audits(),
            static::bookings(),
            static::clinics(),
            static::eligibleAnswers(),
            static::questions(),
            static::reports(),
            static::reportSchedules(),
            static::serviceUsers(),
            static::settings(),
            static::stats(),
            static::users(),
        ];
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function appointments(): Tag
    {
        return Tag::create()
            ->name('Appointments')
            ->description('Appointments at clinics');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function audits(): Tag
    {
        return Tag::create()
            ->name('Audits')
            ->description('User access auditing');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function bookings(): Tag
    {
        return Tag::create()
            ->name('Bookings')
            ->description('For service users to make appointment bookings');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function clinics(): Tag
    {
        return Tag::create()
            ->name('Clinics')
            ->description('Clinic location');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function eligibleAnswers(): Tag
    {
        return Tag::create()
            ->name('Eligible Answers')
            ->description('Set by clinics to specify which answers make the user eligible');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function questions(): Tag
    {
        return Tag::create()
            ->name('Questions')
            ->description('To check eligibility at clinics for service users');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function reports(): Tag
    {
        return Tag::create()
            ->name('Reports')
            ->description('User generated/scheduled reports');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function reportSchedules(): Tag
    {
        return Tag::create()
            ->name('Report Schedules')
            ->description('Schedules for reports to be automatically generated');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function serviceUsers(): Tag
    {
        return Tag::create()
            ->name('Service Users')
            ->description('End-users consuming the service');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function settings(): Tag
    {
        return Tag::create()
            ->name('Settings')
            ->description('Organisation settings');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function stats(): Tag
    {
        return Tag::create()
            ->name('Stats')
            ->description('Dashboard stats');
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Tag
     */
    public static function users(): Tag
    {
        return Tag::create()
            ->name('Users')
            ->description('Backend users');
    }
}
