<?php
use App\Models\User;
use App\Models\Club;
use App\Models\Team;
use App\Models\Squad;
use Illuminate\Support\Facades\DB;

echo "Managers: " . User::where("role", "manager")->count() . "\n";
echo "Clubs: " . Club::count() . "\n";
echo "Teams: " . Team::count() . "\n";
echo "Squads: " . Squad::count() . "\n";
echo "Athletes: " . User::where("role", "sportiv")->count() . "\n";
echo "Parents: " . User::where("role", "parinte")->count() . "\n";
echo "Parent-Student links: " . DB::table("parent_student")->count() . "\n";