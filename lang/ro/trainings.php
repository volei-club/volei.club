<?php

return [
    'title' => 'Antrenamente',
    'subtitle' => 'Gestionează programul săptămânal de antrenamente',
    'add' => 'Adaugă Antrenament',
    'edit' => 'Editează Antrenament',
    'loading' => 'Se încarcă antrenamentele...',

    'filters' => [
        'club' => 'Club',
        'all_clubs' => 'Toate Cluburile',
        'team' => 'Echipă',
        'all_teams' => 'Toate Echipele',
    ],

    'table' => [
        'day' => 'Zi',
        'time' => 'Interval Orar',
        'location' => 'Locație',
        'team' => 'Echipă',
        'coach' => 'Antrenor',
        'actions' => 'Acțiuni',
    ],

    'form' => [
        'club' => 'Club',
        'select_club' => 'Selectează Club',
        'day' => 'Ziua Săptămânii',
        'start' => 'Start',
        'end' => 'Sfârșit',
        'start_date' => 'Dată Început (Opțional)',
        'end_date' => 'Dată Sfârșit (Opțional)',
        'location' => 'Locație',
        'select_location' => 'Selectează Locație',
        'team' => 'Echipă',
        'select_team' => 'Selectează Echipa',
        'coach' => 'Antrenor',
        'select_coach' => 'Selectează Antrenor',
        'days' => [
            'luni' => 'Luni',
            'marti' => 'Marți',
            'miercuri' => 'Miercuri',
            'joi' => 'Joi',
            'vineri' => 'Vineri',
            'sambata' => 'Sâmbătă',
            'duminica' => 'Duminică',
        ],
    ],

    'messages' => [
        'empty_state' => 'Nu există antrenamente programate pentru criteriile selectate.',
        'save_success' => 'Antrenament salvat cu succes!',
        'create_success' => 'Antrenament programat!',
        'update_success' => 'Antrenament actualizat!',
        'delete_confirm' => 'Ești sigur că vrei să ștergi acest antrenament?',
        'delete_success' => 'Antrenament șters cu succes!',
        'delete_error' => 'Eroare la ștergere.',
        'save_error' => 'Eroare la salvarea antrenamentului.',
        'network_error' => 'Eroare de rețea la salvarea antrenamentului.',
        'delete_network_error' => 'Eroare de rețea la ștergere.',
    ],

    'notifications' => [
        'cancellation_title' => 'Sesiune anulată',
        'cancellation_message' => 'Bună ziua! Sesiunea de antrenament din data de :date (:time) a fost ANULATĂ. Motiv: :reason',
        'unspecified_reason' => 'Nespecificat',
        'cancellation_success' => 'Sesiunea de antrenament a fost anulată și notificările au fost trimise.',
        'restore_success' => 'Sesiunea de antrenament a fost restaurată.',
    ],
];
