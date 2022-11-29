<?php
use Visol\Solrmultilangresults\Controller\ResultsController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Visol\Solrmultilangresults\Eid\SearchResultsEid;

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionUtility::configurePlugin(
    'Solrmultilangresults',
    'Solrmultilangresults',
    [
        ResultsController::class => 'index',

    ],
    // non-cacheable actions
    [
    ]
);

// Register EID for search results
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_solrmultilangresults_results'] = SearchResultsEid::class . '::main';
