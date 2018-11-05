<?php

namespace Tests\Feature;

use App\Contracts\Geocoder;
use App\Geocoders\StubGeocoder;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\EligibleAnswer;
use App\Models\Question;
use App\Notifications\Email\ServiceUser\BookingConfirmedEmail as BookingConfirmedServiceUserEmail;
use App\Notifications\Email\User\BookingConfirmedEmail as BookingConfirmedUserEmail;
use App\Notifications\Sms\ServiceUser\BookingConfirmedSms;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingsTest extends TestCase
{
    /*
     * Make a booking.
     */

    public function test_guest_can_make_booking()
    {
        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');
        $checkboxQuestion = Question::createCheckbox('Are you a smoker?');
        $dateQuestion = Question::createDate('What is your date of birth?');
        $textQuestion = Question::createText('Where did you hear about us?');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create();

        // Create the eligible answers for the clinic.
        $clinic->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);
        $clinic->eligibleAnswers()->create([
            'question_id' => $checkboxQuestion->id,
            'answer' => EligibleAnswer::parseCheckboxAnswer(false),
        ]);
        $clinic->eligibleAnswers()->create([
            'question_id' => $dateQuestion->id,
            'answer' => EligibleAnswer::parseDateAnswer([
                'comparison' => '>',
                'interval' => now()->diffInSeconds(now()->subYears(18)),
            ]),
        ]);

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic->id]);

        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => null,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
                [
                    'question_id' => $checkboxQuestion->id,
                    'answer' => false,
                ],
                [
                    'question_id' => $dateQuestion->id,
                    'answer' => now()->subYears(21)->toDateString(),
                ],
                [
                    'question_id' => $textQuestion->id,
                    'answer' => 'Result on a search engine',
                ],
            ],
        ]);

        $appointment->refresh();

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'clinic_id' => $appointment->clinic_id,
            'is_repeating' => false,
            'start_at' => $appointment->start_at->toIso8601String(),
            'did_not_attend' => $appointment->did_not_attend,
        ]);
        $response->assertJsonMissing([
            'service_user_id' => null,
            'booked_at' => null,
            'consented_at' => null,
        ]);
        $this->assertDatabaseHas('service_users', [
            'id' => $appointment->service_user_id,
            'name' => 'John Doe',
            'phone' => '00000000000',
            'email' => null,
            'preferred_contact_method' => 'phone',
        ]);
        $this->assertDatabaseHas('answers', ['question_id' => $selectQuestion->id]);
        $this->assertDatabaseHas('answers', ['question_id' => $checkboxQuestion->id]);
        $this->assertDatabaseHas('answers', ['question_id' => $dateQuestion->id]);
        $this->assertDatabaseHas('answers', ['question_id' => $textQuestion->id]);
        $this->assertDatabaseHas('anonymised_answers', ['question_id' => $selectQuestion->id]);
        $this->assertDatabaseHas('anonymised_answers', ['question_id' => $checkboxQuestion->id]);
        $this->assertDatabaseHas('anonymised_answers', ['question_id' => $dateQuestion->id]);
        $this->assertDatabaseHas('anonymised_answers', ['question_id' => $textQuestion->id]);
    }

    public function test_guest_cannot_book_booked_appointment()
    {
        // Create the question.
        $textQuestion = Question::createText('Where did you hear about us?');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create();

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic->id]);

        // Make the first booking.
        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => null,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $textQuestion->id,
                    'answer' => 'Result on a search engine',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        // Attempt to rebook the same appointment.
        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => null,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $textQuestion->id,
                    'answer' => 'Result on a search engine',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_guest_cannot_book_appointment_in_past()
    {
        // Create the question.
        $textQuestion = Question::createText('Where did you hear about us?');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create();

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'start_at' => today()->subDay(),
        ]);

        // Make the first booking.
        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => null,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $textQuestion->id,
                    'answer' => 'Result on a search engine',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_guest_cannot_booked_appointment_outside_booking_threshold()
    {
        // Fake the current time.
        Carbon::setTestNow(today()->hour(20));

        // Create the question.
        $textQuestion = Question::createText('Where did you hear about us?');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create([
            'appointment_duration' => 60,
            'appointment_booking_threshold' => 600,
        ]);

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'start_at' => today()->addDay(),
        ]);

        // Make the first booking.
        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => null,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $textQuestion->id,
                    'answer' => 'Result on a search engine',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_guest_cannot_book_appointment_for_clinic_they_are_not_eligible_at()
    {
        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create();

        // Create the eligible answers for the clinic.
        $clinic->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic->id]);

        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => null,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Female',
                ]
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_guest_cannot_book_appointment_for_clinic_that_has_not_updated_eligible_answers()
    {
        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create();

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic->id]);

        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => null,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ]
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_booking_sms_notification_sent_to_service_user_when_booked()
    {
        Queue::fake();

        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create();

        // Create the eligible answers for the clinic.
        $clinic->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic->id]);

        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => null,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        Queue::assertPushed(BookingConfirmedSms::class);
    }

    public function test_booking_email_notification_sent_to_service_user_when_booked()
    {
        Queue::fake();

        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create();

        // Create the eligible answers for the clinic.
        $clinic->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic->id]);

        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => $this->faker->safeEmail,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        Queue::assertPushed(BookingConfirmedServiceUserEmail::class);
    }

    public function test_booking_email_notification_sent_to_community_worker_when_booked()
    {
        Queue::fake();

        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');

        // Create the clinic.
        $clinic = factory(Clinic::class)->create();

        // Create the eligible answers for the clinic.
        $clinic->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);

        // Create an appointment at the clinic.
        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic->id]);

        $response = $this->json('POST', '/v1/bookings', [
            'appointment_id' => $appointment->id,
            'service_user' => [
                'name' => 'John Doe',
                'phone' => '00000000000',
                'email' => $this->faker->safeEmail,
                'preferred_contact_method' => 'phone',
            ],
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        Queue::assertPushed(BookingConfirmedUserEmail::class);
    }

    /*
     * Check eligibility.
     */

    public function test_guest_can_check_eligibility()
    {
        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');
        $checkboxQuestion = Question::createCheckbox('Are you a smoker?');
        $dateQuestion = Question::createDate('What is your date of birth?');
        $textQuestion = Question::createText('Where did you hear about us?');

        // Make the request.
        $response = $this->json('POST', '/v1/bookings/eligibility', [
            'postcode' => $this->faker->postcode,
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
                [
                    'question_id' => $checkboxQuestion->id,
                    'answer' => false,
                ],
                [
                    'question_id' => $dateQuestion->id,
                    'answer' => now()->subYears(21)->toDateString(),
                ],
                [
                    'question_id' => $textQuestion->id,
                    'answer' => 'Result on a search engine',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        // There should be no clinics returned since no clinics have been created.
        $response->assertJson(['data' => []]);
    }

    public function test_ineligible_clinic_does_not_show_up()
    {
        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');
        $checkboxQuestion = Question::createCheckbox('Are you a smoker?');
        $dateQuestion = Question::createDate('What is your date of birth?');
        $textQuestion = Question::createText('Where did you hear about us?');

        // Create an eligible clinic.
        $eligibleClinic = factory(Clinic::class)->create();
        $eligibleClinic->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);
        $eligibleClinic->eligibleAnswers()->create([
            'question_id' => $checkboxQuestion->id,
            'answer' => EligibleAnswer::parseCheckboxAnswer(false),
        ]);
        $eligibleClinic->eligibleAnswers()->create([
            'question_id' => $dateQuestion->id,
            'answer' => EligibleAnswer::parseDateAnswer([
                'comparison' => '>',
                'interval' => now()->diffInSeconds(now()->subYears(18)),
            ]),
        ]);

        // Create an uneligible clinic.
        $uneligibleClinic = factory(Clinic::class)->create();
        $uneligibleClinic->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Female'], $selectQuestion),
        ]);
        $uneligibleClinic->eligibleAnswers()->create([
            'question_id' => $checkboxQuestion->id,
            'answer' => EligibleAnswer::parseCheckboxAnswer(false),
        ]);
        $uneligibleClinic->eligibleAnswers()->create([
            'question_id' => $dateQuestion->id,
            'answer' => EligibleAnswer::parseDateAnswer([
                'comparison' => '>',
                'interval' => now()->diffInSeconds(now()->subYears(18)),
            ]),
        ]);

        // Create a clinic which has not updated their eligible answers.
        $noEligibleAnswersClinic = factory(Clinic::class)->create();

        // Make the request.
        $response = $this->json('POST', '/v1/bookings/eligibility', [
            'postcode' => $this->faker->postcode,
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
                [
                    'question_id' => $checkboxQuestion->id,
                    'answer' => false,
                ],
                [
                    'question_id' => $dateQuestion->id,
                    'answer' => now()->subYears(21)->toDateString(),
                ],
                [
                    'question_id' => $textQuestion->id,
                    'answer' => 'Result on a search engine',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonFragment(['id' => $eligibleClinic->id]);
        $response->assertJsonMissing(['id' => $uneligibleClinic->id]);
        $response->assertJsonMissing(['id' => $noEligibleAnswersClinic->id]);
    }

    public function test_eligible_clinics_are_ordered_by_distance_with_postcode()
    {
        $this->app->singleton(Geocoder::class, StubGeocoder::class);

        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');

        // Create the eligible clinics.
        $clinic1 = factory(Clinic::class)->create();
        $clinic1->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);
        $clinic1->lat = StubGeocoder::LATITUDE + 1;
        $clinic1->lon = StubGeocoder::LONGITUDE + 1;
        $clinic1->save();

        $clinic2 = factory(Clinic::class)->create();
        $clinic2->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);
        $clinic2->lat = StubGeocoder::LATITUDE + 2;
        $clinic2->lon = StubGeocoder::LONGITUDE + 2;
        $clinic2->save();

        $clinic3 = factory(Clinic::class)->create();
        $clinic3->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);
        $clinic3->lat = StubGeocoder::LATITUDE + 3;
        $clinic3->lon = StubGeocoder::LONGITUDE + 3;
        $clinic3->save();

        // Make the request.
        $response = $this->json('POST', '/v1/bookings/eligibility', [
            'postcode' => $this->faker->postcode,
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonFragment(['id' => $clinic1->id]);
        $response->assertJsonFragment(['id' => $clinic2->id]);
        $response->assertJsonFragment(['id' => $clinic3->id]);

        $content = json_decode($response->getContent(), true);
        $clinicIds = array_pluck($content['data'], 'id');
        $this->assertEquals([$clinic1->id, $clinic2->id, $clinic3->id], $clinicIds);
    }

    public function test_eligible_clinics_are_ordered_by_distance_with_location()
    {
        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');

        // Create the eligible clinics.
        $clinic1 = factory(Clinic::class)->create();
        $clinic1->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);
        $clinic1->lat = StubGeocoder::LATITUDE + 1;
        $clinic1->lon = StubGeocoder::LONGITUDE + 1;
        $clinic1->save();

        $clinic2 = factory(Clinic::class)->create();
        $clinic2->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);
        $clinic2->lat = StubGeocoder::LATITUDE + 2;
        $clinic2->lon = StubGeocoder::LONGITUDE + 2;
        $clinic2->save();

        $clinic3 = factory(Clinic::class)->create();
        $clinic3->eligibleAnswers()->create([
            'question_id' => $selectQuestion->id,
            'answer' => EligibleAnswer::parseSelectAnswer(['Male'], $selectQuestion),
        ]);
        $clinic3->lat = StubGeocoder::LATITUDE + 3;
        $clinic3->lon = StubGeocoder::LONGITUDE + 3;
        $clinic3->save();

        // Make the request.
        $response = $this->json('POST', '/v1/bookings/eligibility', [
            'location' => [
                'lat' => StubGeocoder::LATITUDE,
                'lon' => StubGeocoder::LONGITUDE,
            ],
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonFragment(['id' => $clinic1->id]);
        $response->assertJsonFragment(['id' => $clinic2->id]);
        $response->assertJsonFragment(['id' => $clinic3->id]);

        $content = json_decode($response->getContent(), true);
        $clinicIds = array_pluck($content['data'], 'id');
        $this->assertEquals([$clinic1->id, $clinic2->id, $clinic3->id], $clinicIds);
    }

    public function test_cannot_check_eligiblity_without_postcode_or_location()
    {
        // Create the questions.
        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');

        // Make the request.
        $response = $this->json('POST', '/v1/bookings/eligibility', [
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => 'Male',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
