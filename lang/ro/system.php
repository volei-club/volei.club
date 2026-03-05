<?php

return [
    'title' => 'Sistem & Mentenanță',
    'subtitle' => 'Gestionare exporturi, importuri și date globale.',

    'export' => [
        'title' => 'Export Date',
        'subtitle' => 'Descarcă datele sistemului în format JSON.',
        'all' => [
            'title' => 'Tot Sistemul',
            'desc' => 'Backup complet (toate tabelele)',
        ],
        'preparing' => 'Se pregătește exportul pentru: :type...',
        'success' => 'Export finalizat cu succes!',
        'error' => 'Eroare la exportul datelor',
    ],

    'import' => [
        'title' => 'Import Date',
        'subtitle' => 'Încarcă un fișier JSON pentru actualizare/adăugare.',
        'dropzone' => [
            'placeholder' => 'Alege un fișier JSON sau trage-l aici',
            'max_size' => 'Maxim 50MB per fișier',
        ],
        'warning' => [
            'label' => 'Atenție:',
            'text' => 'Elementele care au un <strong>ID</strong> identic în sistem vor fi <strong>suprascrise</strong>. Elementele noi (fără ID) vor fi adăugate automat.',
        ],
        'button' => 'Lansează Importul',
        'importing' => 'Se importă datele...',
        'success' => 'Import finalizat cu succes!',
        'error' => 'Eroare la importul datelor',
        'network_error' => 'Eroare de rețea la import',
    ],

    'types' => [
        'users' => 'Membri',
        'clubs' => 'Cluburi',
        'teams' => 'Grupe',
        'squads' => 'Echipe',
        'locations' => 'Locații',
        'subscriptions' => 'Planuri',
        'trainings' => 'Antrenamente',
        'user-subscriptions' => 'Abonări',
    ],
];
