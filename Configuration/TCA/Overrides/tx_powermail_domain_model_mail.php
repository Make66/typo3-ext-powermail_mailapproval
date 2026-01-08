<?php
defined('TYPO3') or die();

// Add approved field to TCA
$tempColumns = [
    'approved' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:powermail_mailapproval/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_mail.approved',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'default' => 0,
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_powermail_domain_model_mail',
    $tempColumns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_powermail_domain_model_mail',
    'approved',
    '',
    'after:crdate'
);
