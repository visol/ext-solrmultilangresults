<?php
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionUtility::registerPlugin(
    'solrmultilangresults',
    'Solrmultilangresults',
    'Solr Multi Language Results Hint'
);

ExtensionManagementUtility::addStaticFile('solrmultilangresults', 'Configuration/TypoScript', 'Solr Multi-Language results');
