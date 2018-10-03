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
    /*
     * List them.
     */

    public function test_guest_can_list_them()
    {
        $checkboxQuestion = Question::createCheckbox('Are you over 18?');
        $dateQuestion = Question::createDate('What is your date of birth?');
        $selectQuestion = Question::createSelect('What is your sex?', 'Male', 'Female');
        $textQuestion = Question::createText('Where did you hear about us?');

        $response = $this->json('GET', '/v1/questions');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $checkboxQuestion->id,
                'question' => 'Are you over 18?',
                'type' => Question::CHECKBOX,
            ]
        ]);
        $response->assertJsonFragment([
            [
                'id' => $dateQuestion->id,
                'question' => 'What is your date of birth?',
                'type' => Question::DATE,
            ]
        ]);
        $response->assertJsonFragment([
            [
                'id' => $selectQuestion->id,
                'question' => 'What is your sex?',
                'options' => ['Male', 'Female'],
                'type' => Question::SELECT,
            ]
        ]);
        $response->assertJsonFragment([
            [
                'id' => $textQuestion->id,
                'question' => 'Where did you hear about us?',
                'type' => Question::TEXT,
            ]
        ]);
    }

    /*
     * Create them.
     */

    public function test_guest_cannot_create_them()
    {
        $response = $this->json('POST', '/v1/questions');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_create_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/questions');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_create_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/questions');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_create_them()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/questions', [
            'questions' => [
                ['type' => Question::CHECKBOX, 'question' => 'Are you over 18?'],
                ['type' => Question::DATE, 'question' => 'What is your date of birth?'],
                ['type' => Question::SELECT, 'question' => 'What is your sex?', 'options' => ['Male', 'Female']],
                ['type' => Question::TEXT, 'question' => 'Where did you hear about us?'],
            ]
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('questions', [
            'question' => 'Are you over 18?',
            'type' => Question::CHECKBOX,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('questions', [
            'question' => 'What is your date of birth?',
            'type' => Question::DATE,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('questions', [
            'question' => 'What is your sex?',
            'type' => Question::SELECT,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('question_options', ['option' => 'Male']);
        $this->assertDatabaseHas('question_options', ['option' => 'Female']);
        $this->assertDatabaseHas('questions', [
            'question' => 'Where did you hear about us?',
            'type' => Question::TEXT,
            'deleted_at' => null,
        ]);
    }
}
