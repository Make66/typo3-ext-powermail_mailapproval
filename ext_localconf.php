<?php
defined('TYPO3') or die();

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

// Configure plugin for Pi2 filtering
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_ListViewExtended'][] = 
    \Taketool\PowermailMailapproval\Hooks\ListViewHook::class;

// Register icon
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Imaging\IconRegistry::class
);
$iconRegistry->registerIcon(
    'powermail_mailapproval-module',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:powermail_mailapproval/Resources/Public/Icons/Extension.svg']
);
