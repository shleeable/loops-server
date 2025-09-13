<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'create-admin-account';

    /**
     * The console command description.
     */
    protected $description = 'Create a new admin user for the Loops application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating a new admin user for Loops...');
        $this->newLine();

        try {
            $name = text(
                label: 'Full Name',
                placeholder: 'Enter the admin\'s full name',
                required: true,
                validate: fn (string $value) => match (true) {
                    strlen($value) < 2 => 'Name must be at least 2 characters.',
                    strlen($value) > 255 => 'Name must not exceed 255 characters.',
                    default => null
                }
            );

            $username = text(
                label: 'Username',
                placeholder: 'Enter a unique username',
                required: true,
                validate: function (string $value) {
                    if (strlen($value) < 3) {
                        return 'Username must be at least 3 characters.';
                    }
                    if (strlen($value) > 50) {
                        return 'Username must not exceed 50 characters.';
                    }
                    if (! preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
                        return 'Username can only contain letters, numbers, underscores, and hyphens.';
                    }
                    if (User::where('username', $value)->exists()) {
                        return 'Username already exists.';
                    }

                }
            );

            $email = text(
                label: 'Email Address',
                placeholder: 'admin@example.com',
                required: true,
                validate: function (string $value) {
                    $validator = Validator::make(['email' => $value], [
                        'email' => 'required|email|max:255',
                    ]);

                    if ($validator->fails()) {
                        return 'Please enter a valid email address.';
                    }

                    if (User::where('email', $value)->exists()) {
                        return 'Email address already exists.';
                    }

                }
            );

            $password = password(
                label: 'Password',
                placeholder: 'Enter a secure password',
                required: true,
                validate: fn (string $value) => match (true) {
                    strlen($value) < 8 => 'Password must be at least 8 characters.',
                    default => null
                }
            );

            $confirmPassword = password(
                label: 'Confirm Password',
                placeholder: 'Re-enter the password',
                required: true,
                validate: fn (string $value) => $value !== $password
                    ? 'Password confirmation does not match.'
                    : null
            );

            $this->newLine();
            $this->info('Admin User Summary:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Name', $name],
                    ['Username', $username],
                    ['Email', $email],
                    ['Password', str_repeat('*', strlen($password))],
                ]
            );

            $confirmed = confirm(
                label: 'Create this admin user?',
                default: true
            );

            if (! $confirmed) {
                $this->warn('Admin user creation cancelled.');

                return Command::FAILURE;
            }

            DB::transaction(function () use ($name, $username, $email, $password) {
                $user = new User;
                $user->name = $name;
                $user->username = $username;
                $user->email = $email;
                $user->password = Hash::make($password);
                $user->email_verified_at = now();
                $user->is_admin = true;
                $user->save();

                $this->info('✅ Admin user created successfully!');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['ID', $user->id],
                        ['Name', $user->name],
                        ['Username', $user->username],
                        ['Email', $user->email],
                        ['Created', $user->created_at->format('Y-m-d H:i:s')],
                    ]
                );
            });

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to create admin user: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
