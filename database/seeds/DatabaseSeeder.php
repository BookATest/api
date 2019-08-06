<?php

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\EligibleAnswer;
use App\Models\Question;
use App\Models\User;
use App\Support\Coordinate;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->createClinics(10);

        $this->createUsers(30);

        foreach (range(0, 6) as $daysToAdd) {
            $this->createAppointments(3, Date::today()->addDays($daysToAdd));
        }

        $this->createQuestions();

        $this->createEligibleAnswers();
    }

    /**
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createClinics(int $count): Collection
    {
        $clinics = new Collection();

        foreach (range(1, $count) as $index) {
            $coordinate = $this->locationInLeeds();

            $clinics->push(
                factory(Clinic::class)->create([
                    'lat' => $coordinate->getLatitude(),
                    'lon' => $coordinate->getLongitude(),
                ])
            );
        }

        return $clinics;
    }

    /**
     * Generate a coordinate for a random place within Leeds, UK.
     *
     * @return \App\Support\Coordinate
     */
    protected function locationInLeeds(): Coordinate
    {
        $minLat = 53.728531;
        $maxLat = 53.850248;

        $minLon = -1.701228;
        $maxLon = -1.456397;

        $factor = 1000000;

        return new Coordinate(
            mt_rand($minLat * $factor, $maxLat * $factor) / $factor,
            mt_rand($minLon * $factor, $maxLon * $factor) / $factor
        );
    }

    /**
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createUsers(int $count): Collection
    {
        $users = new Collection();

        foreach (range(1, $count) as $index) {
            /** @var \App\Models\User $user */
            $user = factory(User::class)->create();

            /** @var \App\Models\Clinic $clinic */
            $clinic = Clinic::query()->inRandomOrder()->firstOrFail();

            // Generate a random user role.
            $organisationAdmin = 1;
            $clinicAdmin = 2;
            $communityWorker = 3;
            $seed = mt_rand(1, 3);

            switch ($seed) {
                case $organisationAdmin:
                    $user->makeOrganisationAdmin();
                    break;
                case $clinicAdmin:
                    $user->makeClinicAdmin($clinic);
                    break;
                case $communityWorker:
                    $user->makeCommunityWorker($clinic);
                    break;
            }

            $users->push($user);
        }

        return $users;
    }

    /**
     * @param int $perDay the number of appointments that should be generated
     *                    for one day for a single user
     * @param \Carbon\CarbonInterface $day The day to create
     * @return \Illuminate\Database\Eloquent\Collection the day to create appointments for
     */
    protected function createAppointments(int $perDay, CarbonInterface $day): Collection
    {
        $appointments = new Collection();

        // Loop through each user.
        User::all()->each(function (User $user) use ($perDay, $day, $appointments) {
            // Loop through the clinics that the user works at.
            $user->clinics()->distinct()->get()->each(function (Clinic $clinic) use ($perDay, $day, $appointments, $user) {
                // Loop through the count of appointments to create.
                foreach (range(1, $perDay) as $index) {
                    // Create the appointment.
                    $startAtMinutesIntoDay = mt_rand(1, $clinic->slots) * $clinic->appointment_duration;

                    $appointment = Appointment::query()->updateOrCreate([
                        'user_id' => $user->id,
                        'clinic_id' => $clinic->id,
                        'start_at' => $day->copy()->addMinutes($startAtMinutesIntoDay),
                    ]);
                    $appointments->push($appointment);
                }
            });
        });

        return $appointments;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createQuestions(): Collection
    {
        $questions = new Collection();

        $questions->push(Question::createSelect('What sex are you?', 'Male', 'Female', 'Non-binary'));
        $questions->push(Question::createCheckbox('Are you a smoker?'));
        $questions->push(Question::createDate('What is your date of birth?'));
        $questions->push(Question::createText('Where did you hear about us?'));

        return $questions;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createEligibleAnswers(): Collection
    {
        $eligibleAnswers = new Collection();

        // Loop through all the clinics.
        Clinic::all()->each(function (Clinic $clinic) use ($eligibleAnswers) {
            // Loop through all the questions.
            Question::query()
                ->with('questionOptions')
                ->get()
                ->each(function (Question $question) use ($eligibleAnswers, $clinic) {
                    switch ($question->type) {
                        case Question::SELECT:
                            /** @var \Illuminate\Database\Eloquent\Collection $questionOptions */
                            $questionOptions = $question->questionOptions;
                            $questionOptions = $questionOptions->random(
                                mt_rand(1, $questionOptions->count())
                            );

                            $eligibleAnswer = $clinic->eligibleAnswers()->create([
                                'question_id' => $question->id,
                                'answer' => EligibleAnswer::parseSelectAnswer(
                                    $questionOptions->pluck('option')->toArray(),
                                    $question
                                ),
                            ]);
                            break;
                        case Question::CHECKBOX:
                            $eligibleAnswer = $clinic->eligibleAnswers()->create([
                                'question_id' => $question->id,
                                'answer' => EligibleAnswer::parseCheckboxAnswer(
                                    Arr::random([true, false, null])
                                ),
                            ]);
                            break;
                        case Question::DATE:
                            $seed = mt_rand(0, 1);
                            $comparison = $seed === 0 ? '>' : '<';

                            $eligibleAnswer = $clinic->eligibleAnswers()->create([
                                'question_id' => $question->id,
                                'answer' => EligibleAnswer::parseDateAnswer([
                                    'comparison' => $comparison,
                                    'interval' => Date::now()->diffInSeconds(Date::now()->addYears(18)),
                                ]),
                            ]);
                            break;
                        case Question::TEXT:
                        default:
                            return;
                    }

                    $eligibleAnswers->push($eligibleAnswer);
                });
        });

        return $eligibleAnswers;
    }
}
