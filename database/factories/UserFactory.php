<?php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $email = $this->faker->unique()->safeEmail();
        $encryptedEmail = Crypt::encryptString($email);
        $emailHash = hash('sha256', $email);

        return [
            'name' => $this->faker->name(),
            'email' => $encryptedEmail,
            'email_hash' => $emailHash,
            'password' => Hash::make('password'), // Use a default password for testing
            'remember_token' => Str::random(10),
        ];
    }
}
