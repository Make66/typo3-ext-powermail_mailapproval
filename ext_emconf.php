<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Powermail Mail Approval',
    'description' => 'Backend module for approving powermail submissions before they appear in the frontend',
    'category' => 'module',
    'author' => 'Taketool',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'powermail' => '10.0.0-10.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
