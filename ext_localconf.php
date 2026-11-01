<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Register XClass to extend Mail model with approved property
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Powermail\Domain\Model\Mail::class] = [
    'className' => \Taketool\PowermailMailapproval\Domain\Model\Mail::class
];

// Inlined so it works without including a static template
ExtensionManagementUtility::addTypoScript(
    'powermail_mailapproval',
    'setup',
    // Override powermail template resolution so our List.html takes precedence
    'plugin.tx_powermail.view.templateRootPaths.10 = EXT:powermail_mailapproval/Resources/Private/Templates/' . LF
);
