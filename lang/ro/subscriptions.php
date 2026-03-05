<?php

return [
    'title' => 'Tipuri Abonamente',
    'subtitle' => 'Planuri de abonament disponibile pentru sportivi',
    'add' => 'Adaugă Abonament',
    'add_new' => 'Adaugă Abonament Nou',
    'edit' => 'Editează Abonament',
    'loading' => 'Se încarcă abonamentele...',
    'currency' => 'lei',

    'filters' => [
        'all_clubs' => 'Toate Cluburile',
    ],

    'table' => [
        'name' => 'Nume Abonament',
        'price' => 'Preț',
        'period' => 'Perioadă',
        'club' => 'Club',
        'actions' => 'Acțiuni',
    ],

    'mobile' => [
        'price_per' => 'lei / :period',
    ],

    'athlete' => [
        'view_for' => 'Vezi abonamentele pentru:',
        'current_title' => 'Abonament Curent',
        'active_name' => 'Abonament Activ',
        'expires_at' => 'Valabil până la :date',
        'paid_status' => 'Abonament Plătit',
        'pending_status' => 'Plată în Așteptare',
        'no_active' => 'Niciun abonament activ',
        'no_active_desc' => 'Nu ai un plan de abonament valabil în acest moment. Pentru informații suplimentare, te rugăm să contactezi antrenorul sau administrația clubului.',
        'history_title' => 'Istoric Abonamente',
        'history_subtitle' => 'Arhiva tuturor plăților și abonamentelor active',
        'table' => [
            'plan' => 'Plan Abonament',
            'status' => 'Status',
            'activation' => 'Data Activării',
            'expiration' => 'Data Expirării',
        ],
        'empty_history' => 'Nu există abonamente înregistrate pe acest cont.',
        'activation_label' => 'Activare',
        'expiration_label' => 'Expirare',
    ],

    'form' => [
        'name' => 'Nume Abonament',
        'name_placeholder' => 'Ex: Abonament Standard',
        'price' => 'Preț (LEI)',
        'price_placeholder' => 'Ex: 250',
        'period' => 'Perioadă Recurență',
        'club' => 'Club Aparținător',
        'choose_club' => 'Selectează clubul',
        'periods' => [
            '1_saptamana' => 'O Săptămână',
            '2_saptamani' => '2 Săptămâni',
            '1_luna' => 'O Lună',
            '3_luni' => '3 Luni',
            '6_luni' => '6 Luni',
            '1_an' => 'Un An',
        ],
    ],

    'messages' => [
        'empty_state' => 'Acest club nu are definit niciun plan de abonament.',
        'save_success' => 'Abonament salvat cu succes!',
        'create_success' => 'Abonament creat cu succes!',
        'update_success' => 'Abonament actualizat cu succes!',
        'delete_confirm' => 'Sigur dorești ștergerea acestui abonament de club?',
        'delete_success' => 'Abonament șters cu succes!',
        'delete_error' => 'Eroare la ștergere. Posibil există membri activi asociați.',
        'save_error' => 'Eroare la salvare.',
        'network_error' => 'Eroare de rețea.',
    ],
];
