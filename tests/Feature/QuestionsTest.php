<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class QuestionsTest extends TestCase
{
    public function test_guest_cannot_create_questions()
    {
        $response = $this->json('POST', '/v1/questions');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_create_questions()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/questions');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_create_questions()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeClinicAdmin($clinic);

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/questions');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_create_questions()
    {
        $user = factory(User::class)->create();
        $user->makeOrganisationAdmin();

        Passport::actingAs($user);

        $question1 = [
            'question' => 'What is your gender?',
            'type' => Question::SELECT,
            'options' => ['Male', 'Female', 'Non-Binary'],
        ];
        $question2 = [
            'question' => 'Are you a smoker?',
            'type' => Question::CHECKBOX,
        ];
        $questionData = [
            'questions' => [$question1, $question2]
        ];

        $response = $this->json('POST', '/v1/questions', $questionData);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'question' => 'What is your gender?',
            'type' => Question::SELECT,
            'options' => ['Male', 'Female', 'Non-Binary'],
        ]);
        $response->assertJsonFragment([
            'question' => 'Are you a smoker?',
            'type' => Question::CHECKBOX,
        ]);
    }

    public function test_new_questions_deleted_previous_questions()
    {
        $oldQuestion = Question::create([
            'question' => 'Do you have the right to work within the UK?',
            'type' => Question::CHECKBOX,
        ]);
        $user = factory(User::class)->create();
        $user->makeOrganisationAdmin();

        Passport::actingAs($user);

        $question1 = [
            'question' => 'What is your gender?',
            'type' => Question::SELECT,
            'options' => ['Male', 'Female', 'Non-Binary'],
        ];
        $questionData = [
            'questions' => [$question1]
        ];

        $response = $this->json('POST', '/v1/questions', $questionData);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'question' => 'What is your gender?',
            'type' => Question::SELECT,
            'options' => ['Male', 'Female', 'Non-Binary'],
        ]);
        $response->assertJsonMissingExact([
            'question' => $oldQuestion->question,
            'type' => $oldQuestion->type,
        ]);
        $this->assertDatabaseMissing('questions', [
            'question' => $oldQuestion->question,
            'type' => $oldQuestion->type,
            'deleted_at' => null,
        ]);
    }
}
