<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\EligibleAnswer;
use App\Models\Question;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
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
                    'answer' => now()->subYears(21)->toIso8601String(),
                ],
                [
                    'question_id' => $textQuestion->id,
                    'answer' => 'Result on a search engine',
                ],
            ],
        ]);
        dump($response->getContent());

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

    /*
     * Check eligibility.
     */
}
