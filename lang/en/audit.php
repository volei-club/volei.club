<?php

return [
    'title' => 'System Audit',
    'subtitle' => 'Log of activities and changes in the platform',
    'loading' => 'Loading log...',

    'filters' => [
        'all_events' => 'All Events',
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'all_types' => 'All Types',
        'users' => 'Users',
        'subscriptions' => 'Subscriptions',
        'clubs' => 'Clubs',
        'teams' => 'Groups',
        'squads' => 'Squads',
    ],

    'table' => [
        'user' => 'User',
        'action' => 'Action',
        'entity' => 'Entity',
        'modifications' => 'Modifications',
        'date_ip' => 'Date & IP',
        'system_anon' => 'System / Anonymous',
        'view_initial' => 'View initial data',
        'view_deleted' => 'View deleted data',
    ],

    'events' => [
        'created' => 'Created',
        'updated' => 'Edited',
        'deleted' => 'Deleted',
    ],

    'keys' => [
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'is_active' => 'Active',
        'club_id' => 'Club',
        'team_ids' => 'Groups',
        'squad_ids' => 'Squads',
        'status' => 'Status',
        'price' => 'Price',
        'period' => 'Period',
        'address' => 'Address',
        'day_of_week' => 'Day of Week',
        'start_time' => 'Start Time',
        'end_time' => 'End Time',
        'location_id' => 'Location',
        'coach_id' => 'Coach',
        'team_id' => 'Group',
        'starts_at' => 'Start Date',
        'expires_at' => 'Expiration Date',
        'subscription_id' => 'Subscription Type',
    ],

    'types' => [
        'User' => 'User',
        'Club' => 'Club',
        'Subscription' => 'Subscription Definition',
        'UserSubscription' => 'Member Subscription',
        'Team' => 'Group',
        'Squad' => 'Squad',
        'Location' => 'Location',
        'Training' => 'Training',
    ],

    'modal' => [
        'details_title' => 'Object Details',
        'close' => 'Close',
    ],
];
