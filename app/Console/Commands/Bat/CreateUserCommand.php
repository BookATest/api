<?php

namespace App\Console\Commands\Bat;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
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
            $this->assignRoles($user);

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

    /**
     * @param \App\Models\User $user
     */
    protected function assignRoles(User $user)
    {
        $clinics = Clinic::all();
        $roles = Role::all();
        $organisationAdminRole = $roles->firstWhere('name', Role::ORGANISATION_ADMIN);
        $otherRoles = $roles->reject(function (Role $role) {
            return $role->name === Role::ORGANISATION_ADMIN;
        });

        // Attach the organisation admin role.
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $organisationAdminRole->id,
        ]);

        // Attach all non-organisation admin roles to the user.
        foreach ($clinics as $clinic) {
            foreach ($otherRoles as $role) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'clinic_id' => $clinic->id,
                ]);
            }
        }
    }
}
