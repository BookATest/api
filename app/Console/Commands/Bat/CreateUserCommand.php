<?php

declare(strict_types=1);

namespace App\Console\Commands\Bat;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bat:create-user 
        {first-name : The user\'s first name} 
        {last-name : The user\' last name} 
        {email : The user\'s email} 
        {phone-number : The user\'s phone number}
        {--password= : Specify a password or omit for one to be generated}
        {--display-email : If the user\'s email should be displayed}
        {--display-phone : If the user\'s phone should be displayed}
        {--include-calendar-attachment : If a calendar attachment of the user\'s appointments should be sent with emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new user with organisation admin privileges';

    /**
     * Execute the console command.
     *
     * @throws \Throwable
     * @return mixed
     */
    public function handle()
    {
        return DB::transaction(function () {
            // Cache the password to display.
            $password = $this->option('password') ?? Str::random();

            // Create the user record.
            $user = $this->createUser($password);

            // Get all the clinics and roles.
            $user->makeOrganisationAdmin();

            // Output message.
            $this->info('User created successfully.');
            $this->warn("Password: $password");

            return true;
        });
    }

    /**
     * @param string $password
     *
     * @return \App\Models\User
     */
    protected function createUser(string $password): User
    {
        return User::create([
            'first_name' => $this->argument('first-name'),
            'last_name' => $this->argument('last-name'),
            'email' => $this->argument('email'),
            'phone' => $this->argument('phone-number'),
            'password' => bcrypt($password),
            'display_email' => $this->option('display-email'),
            'display_phone' => $this->option('display-phone'),
            'include_calendar_attachment' => $this->option('include-calendar-attachment'),
            'calendar_feed_token' => Str::random(10),
        ]);
    }
}
