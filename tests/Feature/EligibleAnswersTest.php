<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\EligibleAnswer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EligibleAnswersTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_cannot_list_them()
    {
        $clinic = factory(Clinic::class)->create();

        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_list_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_list_them_for_another_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $anotherClinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$anotherClinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_list_them_when_not_created()
    {
        Question::createSelect('What sex are you?', 'Male', 'Female');

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_ca_can_list_select_when_created()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $question = Question::createSelect('What sex are you?', 'Male', 'Female');
        EligibleAnswer::create([
            'clinic_id' => $clinic->id,
            'question_id' => $question->id,
            'answer' => ['Male'],
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'question_id' => $question->id,
            'answer' => ['Male'],
        ]);
    }

    public function test_ca_can_list_all_types_when_created()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $eighteenYearsAgo = Date::now()->subYears(18)->timestamp;

        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');
        $dateQuestion = Question::createDate('What is your date of birth?');
        $checkboxQuestion = Question::createCheckbox('Are you a smoker?');

        EligibleAnswer::create([
            'clinic_id' => $clinic->id,
            'question_id' => $selectQuestion->id,
            'answer' => ['Male'],
        ]);
        EligibleAnswer::create([
            'clinic_id' => $clinic->id,
            'question_id' => $dateQuestion->id,
            'answer' => [
                'comparison' => '>',
                'interval' => $eighteenYearsAgo,
            ],
        ]);
        EligibleAnswer::create([
            'clinic_id' => $clinic->id,
            'question_id' => $checkboxQuestion->id,
            'answer' => false,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'question_id' => $selectQuestion->id,
            'answer' => ['Male'],
        ]);
        $response->assertJsonFragment([
            'question_id' => $dateQuestion->id,
            'answer' => [
                'comparison' => '>',
                'interval' => $eighteenYearsAgo,
            ],
        ]);
        $response->assertJsonFragment([
            'question_id' => $checkboxQuestion->id,
            'answer' => false,
        ]);
    }

    public function test_ca_cannot_see_previous_eligible_answers()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $question = Question::createSelect('What sex are you?', 'Male', 'Female');
        $question->delete();

        Question::createSelect(
            'What sex are you?',
            'Male',
            'Female',
            'Prefer not to say'
        );

        EligibleAnswer::create([
            'clinic_id' => $clinic->id,
            'question_id' => $question->id,
            'answer' => ['Male'],
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_audit_created_when_listed()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $question = Question::createSelect('What sex are you?', 'Male', 'Female');
        EligibleAnswer::create([
            'clinic_id' => $clinic->id,
            'question_id' => $question->id,
            'answer' => ['Male'],
        ]);

        Passport::actingAs($user);
        $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
        });
    }

    public function test_ca_can_list_null_checkbox_when_created()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $question = Question::createCheckbox('Are you a smoker?');
        EligibleAnswer::create([
            'clinic_id' => $clinic->id,
            'question_id' => $question->id,
            'answer' => null,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'question_id' => $question->id,
            'answer' => null,
        ]);
    }

    /*
     * Update them.
     */

    public function test_guest_cannot_update_them()
    {
        $clinic = factory(Clinic::class)->create();

        $response = $this->json('PUT', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_update_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_update_them_for_another_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);
        $anotherClinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/$anotherClinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_can_update_a_new_set_of_only_a_select()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $selectQuestion = Question::createSelect(
            'What is your favourite colour?',
            'Blue',
            'Red',
            'Green'
        );

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/$clinic->id/eligible-answers", [
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => ['Blue', 'Green'],
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'question_id' => $selectQuestion->id,
            'answer' => ['Blue', 'Green'],
        ]);
    }

    public function test_ca_can_update_a_new_set_of_all_types()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');
        $dateQuestion = Question::createDate('What is your date of birth?');
        $checkboxQuestion = Question::createCheckbox('Are you a smoker?');
        Question::createText('Where did you hear about us?');

        $interval = Date::now()->diffInSeconds(now()->subYears(18));

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/$clinic->id/eligible-answers", [
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => ['Male'],
                ],
                [
                    'question_id' => $dateQuestion->id,
                    'answer' => [
                        'comparison' => '>',
                        'interval' => $interval,
                    ],
                ],
                [
                    'question_id' => $checkboxQuestion->id,
                    'answer' => false,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'question_id' => $selectQuestion->id,
            'answer' => ['Male'],
        ]);
        $response->assertJsonFragment([
            'question_id' => $dateQuestion->id,
            'answer' => [
                'comparison' => '>',
                'interval' => $interval,
            ],
        ]);
        $response->assertJsonFragment([
            'question_id' => $checkboxQuestion->id,
            'answer' => false,
        ]);
    }

    public function test_ca_must_provide_answers_for_all_non_text_questions()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $selectQuestion = Question::createSelect('What sex are you?', 'Male', 'Female');
        $dateQuestion = Question::createDate('What is your date of birth?');
        Question::createCheckbox('Are you a smoker?');

        $interval = Date::now()->diffInSeconds(now()->subYears(18));

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/$clinic->id/eligible-answers", [
            'answers' => [
                [
                    'question_id' => $selectQuestion->id,
                    'answer' => ['Male'],
                ],
                [
                    'question_id' => $dateQuestion->id,
                    'answer' => [
                        'comparison' => '>',
                        'interval' => $interval,
                    ],
                ],
                // Purposely missing the checkbox answer here.
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_ca_can_update_a_new_set_of_only_a_null_checkbox()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $checkboxQuestion = Question::createCheckbox('Are you a smoker?');

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/$clinic->id/eligible-answers", [
            'answers' => [
                [
                    'question_id' => $checkboxQuestion->id,
                    'answer' => null,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'question_id' => $checkboxQuestion->id,
            'answer' => null,
        ]);
    }
}
