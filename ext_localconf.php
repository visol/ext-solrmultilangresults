<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Visol.' . $_EXTKEY,
	'Solrmultilangresults',
	array(
		'Results' => 'index',
		
	),
	// non-cacheable actions
	array(
		
	)
);

// Register EID for seach results
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_solrmultilangresults_results'] = 'EXT:' . $_EXTKEY . '/Classes/Eid/SearchResults.php';
