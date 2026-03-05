<?php

return [
    'title' => 'Subscription Types',
    'subtitle' => 'Subscription plans available for athletes',
    'add' => 'Add Subscription',
    'add_new' => 'Add New Subscription',
    'edit' => 'Edit Subscription',
    'loading' => 'Loading subscriptions...',
    'currency' => 'RON',

    'filters' => [
        'all_clubs' => 'All Clubs',
    ],

    'table' => [
        'name' => 'Subscription Name',
        'price' => 'Price',
        'period' => 'Period',
        'club' => 'Club',
        'actions' => 'Actions',
    ],

    'mobile' => [
        'price_per' => 'RON / :period',
    ],

    'athlete' => [
        'view_for' => 'View subscriptions for:',
        'current_title' => 'Current Subscription',
        'active_name' => 'Active Subscription',
        'expires_at' => 'Valid until :date',
        'paid_status' => 'Paid Subscription',
        'pending_status' => 'Payment Pending',
        'no_active' => 'No active subscription',
        'no_active_desc' => 'You do not have a valid subscription plan at this time. For further information, please contact your coach or club administration.',
        'history_title' => 'Subscription History',
        'history_subtitle' => 'Archive of all payments and active subscriptions',
        'table' => [
            'plan' => 'Subscription Plan',
            'status' => 'Status',
            'activation' => 'Activation Date',
            'expiration' => 'Expiration Date',
        ],
        'empty_history' => 'There are no subscriptions registered on this account.',
        'activation_label' => 'Activation',
        'expiration_label' => 'Expiration',
    ],

    'form' => [
        'name' => 'Subscription Name',
        'name_placeholder' => 'Ex: Standard Subscription',
        'price' => 'Price (RON)',
        'price_placeholder' => 'Ex: 250',
        'period' => 'Recurrence Period',
        'club' => 'Belonging Club',
        'choose_club' => 'Select club',
        'periods' => [
            '1_saptamana' => 'One Week',
            '2_saptamani' => '2 Weeks',
            '1_luna' => 'One Month',
            '3_luni' => '3 Months',
            '6_luni' => '6 Months',
            '1_an' => 'One Year',
        ],
    ],

    'messages' => [
        'empty_state' => 'This club has no subscription plan defined.',
        'save_success' => 'Subscription saved successfully!',
        'create_success' => 'Subscription created successfully!',
        'update_success' => 'Subscription updated successfully!',
        'delete_confirm' => 'Are you sure you want to delete this club subscription?',
        'delete_success' => 'Subscription deleted successfully!',
        'delete_error' => 'Error deleting. There might be associated active members.',
        'save_error' => 'Error saving.',
        'network_error' => 'Network error.',
    ],
];
