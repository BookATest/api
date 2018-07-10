<?php

namespace App\Console\Commands\Bat;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bat:create-user 
        {first_name : The user\'s first name} 
        {last_name : The user\' last name} 
        {email : The user\'s email} 
        {phone_number : The user\'s phone number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new user with organisation admin privileges';

    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    /**
     * CreateUserCommand constructor.
     *
     * @param \Illuminate\Database\DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        parent::__construct();

        $this->db = $db;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        return $this->db->transaction(function () {
            // Cache the password to display.
            $password = str_random();

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
            'first_name' => $this->argument('first_name'),
            'last_name' => $this->argument('last_name'),
            'email' => $this->argument('email'),
            'phone_number' => $this->argument('phone_number'),
            'password' => bcrypt($password),
            'display_email' => true,
            'display_phone_number' => true,
            'include_calendar_attachment' => true,
            'calendar_feed_token' => str_random(10),
        ]);
    }
}
