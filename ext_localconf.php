<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// Register XClass to extend Mail model with approved property
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Powermail\Domain\Model\Mail::class] = [
    'className' => \Taketool\PowermailMailapproval\Domain\Model\Mail::class
];

// Register SignalSlot for setting default approval status
$signalSlotDispatcher = GeneralUtility::makeInstance(
    \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
);
$signalSlotDispatcher->connect(
    \In2code\Powermail\Controller\FormController::class,
    'createActionAfterPersist',
    \Taketool\PowermailMailapproval\EventListener\ApprovalHandler::class,
    'setDefaultApprovalStatus'
);

// Configure plugin for Pi2 filtering
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_ListViewExtended'][] = 
    \Taketool\PowermailMailapproval\Hooks\ListViewHook::class;

// Register icon
$iconRegistry = GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Imaging\IconRegistry::class
);
$iconRegistry->registerIcon(
    'powermail_mailapproval-module',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:powermail_mailapproval/Resources/Public/Icons/Extension.svg']
);

// @import is not reliably processed when added via addTypoScript() in TYPO3 11
// (defaultTypoScript_setup is not always run through checkIncludeLines).
// Critical frontend lines are therefore inlined here directly; everything else
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
