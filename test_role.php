<?php
require 'vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'like', '%SIMIONESCU%')->first();
$squads = $user->squads; // Assuming a user has squads
echo "Squads for " . $user->name . ": " . count($squads) . "\n";
if (count($squads) > 0) {
    $squad = $squads->first();
    echo "Users in squad " . $squad->name . ":\n";
    foreach ($squad->users as $u) {
        echo "- " . $u->name . " (" . $u->role . ")\n";
    }
}
else {
    // maybe she has teams which have squads? or we check all squads
    echo "Checking all squads for her:\n";
    $squadsWithHer = \App\Models\Squad::whereHas('users', fn($q) => $q->where('users.id', $user->id))->get();
    echo "Squads containing her: " . $squadsWithHer->count() . "\n";
    foreach ($squadsWithHer as $s) {
        echo "Squad: " . $s->name . "\n";
        foreach ($s->users as $u) {
            echo "  - " . $u->name . " (" . $u->role . ")\n";
        }
    }
}
