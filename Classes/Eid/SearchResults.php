<?php
namespace Visol\Solrmultilangresults\Eid;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use Tx_Solr_Util;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SearchResults
{

    /**
     * @var \Tx_Solr_PiResults_Results
     */
    protected $searcher;

    public function __construct()
    {
        $this->initTSFE();
        /** @var \Tx_Solr_PiResults_Results $searcher */
        $searcher = GeneralUtility::makeInstance('\Tx_Solr_PiResults_Results');
        $searcher->cObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
        $searcher->main('', array());
        $this->searcher = $searcher;
    }

    /**
     * Return the number of results for a Solr query respecting the allowed sites
     *
     * @return int
     */
    public function getNumberOfResults()
    {
        $solrConfiguration = Tx_Solr_Util::getSolrConfiguration();
        $allowedSites = Tx_Solr_Util::resolveSiteHashAllowedSites(
            GeneralUtility::_GP('id'),
            $solrConfiguration['search.']['query.']['allowedSites']
        );
        $q = GeneralUtility::_GP('q');
        /** @var \Tx_Solr_Query $query */
        $query = GeneralUtility::makeInstance('\Tx_Solr_Query', $q);
        $query->setFieldList(array('title', 'url', 'teaser', 'score'));
        $query->setUserAccessGroups(explode(',', $GLOBALS['TSFE']->gr_list));
        $query->setSiteHashFilter($allowedSites);
        $this->searcher->getSearch()->search($query);
        $response = $this->searcher->getSearch()->getResponse();
        if (1 > $response->response->numFound) {
            $query->useRawQueryString(true);
            $response = $this->searcher->getSearch()->search($query);
        }
        return (int)$response->response->numFound;
    }

    /**
     * Initializes TSFE and sets $GLOBALS['TSFE'].
     *
     * @return void
     */
    protected function initTSFE()
    {
        $pageId = GeneralUtility::_GP('id');
        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $tsfe */
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
            $GLOBALS['TYPO3_CONF_VARS'],
            $pageId,
            ''
        );

        \TYPO3\CMS\Frontend\Utility\EidUtility::initLanguage();
        \TYPO3\CMS\Frontend\Utility\EidUtility::initTCA();

        $GLOBALS['TSFE']->initFEuser();
        // We do not want (nor need) EXT:realurl to be invoked:
        //$GLOBALS['TSFE']->checkAlternativeIdMethods();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();
        if ($pageId > 0) {
            $GLOBALS['TSFE']->settingLanguage();
        }
        $GLOBALS['TSFE']->settingLocale();
    }


}

/**
 * @var $searchResults \Visol\Solrmultilangresults\Eid\SearchResults
 */
$searchResults = GeneralUtility::makeInstance('Visol\\Solrmultilangresults\\Eid\\SearchResults');

// Get and JSONify the number of results for a query
$data = json_encode(array('numberOfResults' => $searchResults->getNumberOfResults()));
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Length: ' . strlen($data));
header('Content-Type: application/json; charset=utf-8');
header('Content-Transfer-Encoding: 8bit');
echo($data);