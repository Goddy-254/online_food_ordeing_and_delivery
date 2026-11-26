<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Bootstrap the first administrator from the local/server command line.
// Only an existing account can be promoted, so no shared default password is created.
Artisan::command('admin:promote {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("No account exists with email [{$email}]. Create the account first, then run this command again.");
        return 1;
    }

    if ($user->role === 'admin') {
        $this->info("{$user->email} is already an administrator.");
        return 0;
    }

    if (! $this->confirm("Promote {$user->name} ({$user->email}) to administrator?", false)) {
        $this->comment('No changes made.');
        return 0;
    }

    $user->update(['role' => 'admin']);
    $this->info("{$user->email} can now sign in at /admin/login.");

    return 0;
})->purpose('Promote an existing account to administrator');
