<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Console\Command;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Cache the password to display.
        $password = str_random();

        // Create the user record.
        $user = $this->createUser($password);

        // Get all the clinics and roles.
        $this->assignRoles($user);

        // Output message.
        $this->info('User created!');
        $this->warn("Password: $password");

        return true;
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

    /**
     * @param \App\Models\User $user
     */
    protected function assignRoles(User $user)
    {
        $clinics = Clinic::all();
        $roles = Role::where('name', '!=', Role::ORGANISATION_ADMIN)->get();

        // Attach all non-organisation admin roles to the user.
        foreach ($clinics as $clinic) {
            foreach ($roles as $role) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'clinic_id' => $clinic->id,
                ]);
            }
        }
    }
}
