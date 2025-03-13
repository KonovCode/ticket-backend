<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-super-user {email} {name} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create super user and generate api token';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
       $user = User::query()->where('email', $this->argument('email'))->first();

       if(!$user) {
           $user = UserFactory::new()->create([
               'name' => $this->argument('name'),
               'email' => $this->argument('email'),
               'password' => Hash::make($this->argument('password')),
           ]);

           $user->createToken('api-token', ['ticket-crud']);

           $this->info('Super user created');
       } else {
           $this->error('Super user already exists');
       }
    }
}
