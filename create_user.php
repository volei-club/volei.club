<?php
\App\Models\User::firstOrCreate(
['email' => 'ion@exemplu.ro'],
[
    'name' => 'Ion Popescu',
    'password' => \Illuminate\Support\Facades\Hash::make('parola123')
]
);
echo "User successfully created.\n";
