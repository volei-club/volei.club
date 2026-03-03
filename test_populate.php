<?php
use App\Models\User;
use App\Models\Club;
use App\Models\Team;
use App\Models\Squad;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

$faker = Faker::create();
$password = Hash::make('password');

echo "Starting seeding...\n";

DB::transaction(function () use ($faker, $password) {
    for ($i = 0; $i < 1; $i++) {
        echo "Creating manager and club " . ($i + 1) . "/30...\n";
        
        $manager = User::create([
            'name' => $faker->name,
            'email' => "manager" . ($i + 1) . "_" . uniqid() . "@example.com",
            'phone' => $faker->phoneNumber,
            'password' => $password,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $club = Club::create([
            'name' => "Club " . $faker->company,
            'created_by' => $manager->id
        ]);

        $manager->update(['club_id' => $club->id]);

        for ($j = 0; $j < 2; $j++) {
            $team = Team::create([
                'club_id' => $club->id,
                'name' => "Grupa " . ($j + 1) . " - " . $club->name
            ]);

            for ($k = 0; $k < 2; $k++) {
                $squad = Squad::create([
                    'name' => "Echipa " . ($k + 1) . " - " . $team->name,
                    'team_id' => $team->id,
                    'created_by' => $manager->id
                ]);

                for ($l = 0; $l < 13; $l++) {
                    $athlete = User::create([
                        'name' => $faker->name,
                        'email' => "athlete" . uniqid() . "@example.com",
                        'phone' => $faker->phoneNumber,
                        'password' => $password,
                        'role' => 'sportiv',
                        'club_id' => $club->id,
                        'is_active' => true,
                    ]);

                    $athlete->teams()->attach($team->id);
                    $athlete->squads()->attach($squad->id);

                    for ($m = 0; $m < 2; $m++) {
                        $parent = User::create([
                            'name' => $faker->name,
                            'email' => "parent" . uniqid() . "@example.com",
                            'phone' => $faker->phoneNumber,
                            'password' => $password,
                            'role' => 'parinte',
                            'club_id' => $club->id,
                            'is_active' => true,
                        ]);

                        DB::table('parent_student')->insert([
                            'parent_id' => $parent->id,
                            'student_id' => $athlete->id
                        ]);
                    }
                }
            }
        }
    }
});

echo "Seeding completed successfully!\n";
