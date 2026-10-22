<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'sitrans:super-admin
        {email : Email address of the platform administrator}
        {--name= : Display name}
        {--password= : Password (a random one is generated when omitted)}';

    protected $description = 'Create or promote a user to platform super admin (accesses /admin)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = $this->option('password') !== null
            ? (string) $this->option('password')
            : Str::password(16);

        $user = User::query()->firstOrNew(['email' => $email]);

        $user->fill([
            'name' => $this->option('name') ?: ($user->name ?: 'Super Admin'),
            'password' => $password,
            'is_super_admin' => true,
        ])->save();

        $this->info("Super admin siap: {$user->email}");

        if ($this->option('password') === null) {
            $this->line("Kata sandi: <options=bold>{$password}</>");
        }

        return self::SUCCESS;
    }
}
