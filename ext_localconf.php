<?php
use Visol\Solrmultilangresults\Controller\ResultsController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
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

// Register EID for seach results
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_solrmultilangresults_results'] = 'EXT:' . 'solrmultilangresults' . '/Classes/Eid/SearchResults.php';
