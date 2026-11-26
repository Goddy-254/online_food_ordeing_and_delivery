<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/** Create the first administrator without exposing role assignment publicly. */
class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create an administrator account for the admin portal';

    public function handle(): int
    {
        $name = trim($this->ask('Administrator name', 'Store Administrator'));
        $email = strtolower(trim($this->ask('Administrator email')));
        $password = $this->secret('Administrator password (minimum 8 characters)');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->info("Administrator account created for {$email}.");

        return self::SUCCESS;
    }
}
