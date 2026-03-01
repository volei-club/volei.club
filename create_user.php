<?php
\App\Models\User::firstOrCreate(
['email' => 'isacescua@gmail.com'],
[
    'name' => 'Andrei Isacescu',
    'password' => \Illuminate\Support\Facades\Hash::make('Password20!!')
]
);
echo "User successfully created.\n";
