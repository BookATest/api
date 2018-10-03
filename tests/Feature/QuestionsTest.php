<?php

namespace Tests\Feature;

use App\Models\Question;
use Illuminate\Http\Response;
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
}
