<?php

return [
    'title' => 'System & Maintenance',
    'subtitle' => 'Manage exports, imports, and global data.',

    'export' => [
        'title' => 'Data Export',
        'subtitle' => 'Download system data in JSON format.',
        'all' => [
            'title' => 'Entire System',
            'desc' => 'Complete backup (all tables)',
        ],
        'preparing' => 'Preparing export for: :type...',
        'success' => 'Export completed successfully!',
        'error' => 'Error exporting data',
    ],

    'import' => [
        'title' => 'Data Import',
        'subtitle' => 'Upload a JSON file for updating/adding.',
        'dropzone' => [
            'placeholder' => 'Choose a JSON file or drag it here',
            'max_size' => 'Maximum 50MB per file',
        ],
        'warning' => [
            'label' => 'Attention:',
            'text' => 'Items with an identical <strong>ID</strong> in the system will be <strong>overwritten</strong>. New items (without ID) will be added automatically.',
        ],
        'button' => 'Launch Import',
        'importing' => 'Importing data...',
        'success' => 'Import completed successfully!',
        'error' => 'Error importing data',
        'network_error' => 'Network error during import',
    ],

    'types' => [
        'users' => 'Members',
        'clubs' => 'Clubs',
        'teams' => 'Groups',
        'squads' => 'Teams',
        'locations' => 'Locations',
        'subscriptions' => 'Plans',
        'trainings' => 'Trainings',
        'user-subscriptions' => 'Subscriptions',
    ],
];
