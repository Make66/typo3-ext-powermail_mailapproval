<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Register XClass to extend Mail model with approved property
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Powermail\Domain\Model\Mail::class] = [
    'className' => \Taketool\PowermailMailapproval\Domain\Model\Mail::class
];

// Configure plugin for Pi2 filtering
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_ListViewExtended'][] = 
    \Taketool\PowermailMailapproval\Hooks\ListViewHook::class;

// Critical frontend lines are inlined here directly; everything else
// (backend module config etc.) stays in setup.typoscript and is loaded via the
// static template registration in Configuration/TCA/Overrides/sys_template.php.
ExtensionManagementUtility::addTypoScript(
    'powermail_mailapproval',
    'setup',
    // Override powermail template resolution so our List.html takes precedence
    'plugin.tx_powermail.view.templateRootPaths.10 = EXT:powermail_mailapproval/Resources/Private/Templates/' . LF
);

/*// XCLASS for MailRepository which lacks functionality
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']
[\In2code\Powermail\Domain\Repository\MailRepository::class] = [
    'className' => \Taketool\PowermailMailapproval\Xclass\MailRepository::class
];*/
