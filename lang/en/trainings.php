<?php

return [
    'title' => 'Trainings',
    'subtitle' => 'Manage weekly training schedule',
    'add' => 'Add Training',
    'edit' => 'Edit Training',
    'loading' => 'Loading trainings...',

    'filters' => [
        'club' => 'Club',
        'all_clubs' => 'All Clubs',
        'team' => 'Team',
        'all_teams' => 'All Teams',
    ],

    'table' => [
        'day' => 'Day',
        'time' => 'Time Range',
        'location' => 'Location',
        'team' => 'Team',
        'coach' => 'Coach',
        'actions' => 'Actions',
    ],

    'form' => [
        'club' => 'Club',
        'select_club' => 'Select Club',
        'day' => 'Day of Week',
        'start' => 'Start',
        'end' => 'End',
        'start_date' => 'Start Date (Optional)',
        'end_date' => 'End Date (Optional)',
        'location' => 'Location',
        'select_location' => 'Select Location',
        'team' => 'Team',
        'select_team' => 'Select Team',
        'coach' => 'Coach',
        'select_coach' => 'Select Coach',
        'days' => [
            'luni' => 'Monday',
            'marti' => 'Tuesday',
            'miercuri' => 'Wednesday',
            'joi' => 'Thursday',
            'vineri' => 'Friday',
            'sambata' => 'Saturday',
            'duminica' => 'Sunday',
        ],
    ],

    'messages' => [
        'empty_state' => 'No trainings scheduled for selected criteria.',
        'save_success' => 'Training saved successfully!',
        'create_success' => 'Training scheduled!',
        'update_success' => 'Training updated!',
        'delete_confirm' => 'Are you sure you want to delete this training?',
        'delete_success' => 'Training deleted successfully!',
        'delete_error' => 'Error deleting.',
        'save_error' => 'Error saving training.',
        'network_error' => 'Network error saving training.',
        'delete_network_error' => 'Network error deleting.',
    ],

    'notifications' => [
        'cancellation_title' => 'Session Cancelled',
        'cancellation_message' => 'Hello! The training session on :date (:time) has been CANCELLED. Reason: :reason',
        'unspecified_reason' => 'Unspecified',
        'cancellation_success' => 'Training session cancelled and notifications sent.',
        'restore_success' => 'Training session restored.',
    ],
];
