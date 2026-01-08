<?php

return [
    'web_PowermailMailapprovalApproval' => [
        'parent' => 'web',
        'position' => ['after' => 'web_list'],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/web/PowermailMailapprovalApproval',
        'labels' => 'LLL:EXT:powermail_mailapproval/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'PowermailMailapproval',
        'iconIdentifier' => 'powermail_mailapproval-module',
        'controllerActions' => [
            \Taketool\PowermailMailapproval\Controller\ApprovalController::class => [
                'list', 'approve', 'reject'
            ],
        ],
    ],
];
