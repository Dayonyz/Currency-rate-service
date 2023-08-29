<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $token = User::factory()->create([
             'name' => 'Test User',
             'email' => 'test@example.com',
         ])->createToken(config('app.name'))->plainTextToken;

         Storage::disk('public')->put('test-plain-token.txt', $token);
    }
}
