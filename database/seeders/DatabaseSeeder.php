<?php

namespace Database\Seeders;

use App\Models\Ceremony;
use App\Models\Couple;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Époux 1 (admin) + époux 2
        $spouse1 = User::factory()->admin()->create([
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $spouse2 = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Création du couple
        $couple = Couple::create([
            'spouse_1_id'        => $spouse1->id,
            'spouse_2_id'        => $spouse2->id,
            'ceremony_date'      => now()->addMonths(6)->toDateString(),
            'ceremony_location'  => 'Paris, France',
        ]);

        // Rattachement des époux au couple
        $spouse1->update(['couple_id' => $couple->id]);
        $spouse2->update(['couple_id' => $couple->id]);

        // Création de la cérémonie associée
        Ceremony::create([
            'couple_id' => $couple->id,
            'program'   => [],
            'status'    => 'draft',
        ]);
    }
}
