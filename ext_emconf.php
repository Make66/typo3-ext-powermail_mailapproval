<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Powermail Mail Approval',
    'description' => 'Backend module for approving powermail submissions before they appear in the frontend',
    'category' => 'module',
    'author' => 'Taketool',
    'author_email' => '',
    'state' => 'stable',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'powermail' => '12.5.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
