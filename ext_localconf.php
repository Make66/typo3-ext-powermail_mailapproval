<?php
defined('TYPO3') or die();

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
