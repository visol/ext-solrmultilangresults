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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Localization\Locales;
use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\ReturnFields;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSetService;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteHashService;
use ApacheSolrForTypo3\Solr\Query;
use ApacheSolrForTypo3\Solr\Search;
use ApacheSolrForTypo3\Solr\Util;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;

class SearchResults
{

    /**
     * @var SearchResultSetService
     */
    protected $searcher;

    public function __construct()
    {
        $this->initTSFE();
        /** @var ConnectionManager $solrConnection */
        $solrConnection = GeneralUtility::makeInstance(ConnectionManager::class)->getConnectionByPageId(
            $GLOBALS['TSFE']->id,
            GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id'),
            $GLOBALS['TSFE']->MP
        );
        $search = GeneralUtility::makeInstance(Search::class, $solrConnection);
        /** @var SearchResultSetService $searcher */
        $searcher = GeneralUtility::makeInstance(SearchResultSetService::class, Util::getSolrConfiguration(), $search);
        $searcher->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->searcher = $searcher;
    }

    /**
     * Return the number of results for a Solr query respecting the allowed sites
     *
     * @return int
     */
    public function getNumberOfResults()
    {
        $solrConfiguration = Util::getSolrConfiguration();

        /** @var $siteHashService SiteHashService */
        $siteHashService = GeneralUtility::makeInstance(SiteHashService::class);
        $allowedSites = $siteHashService->getAllowedSitesForPageIdAndAllowedSitesConfiguration(
            GeneralUtility::_GP((int)'id'),
            $solrConfiguration->getValueByPath('plugin.tx_solr.search.query.allowedSites', '__solr_current_site')
        );
        $q = GeneralUtility::_GP('q');
        /** @var Query $query */
        $query = GeneralUtility::makeInstance(Query::class, $q);
        $returnFields = ReturnFields::fromArray(['title', 'url', 'teaser', 'score']);
        $query->setReturnFields($returnFields);
        $query->setUserAccessGroups(explode(',', implode(',', GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'groupIds'))));
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
        /** @var TypoScriptFrontendController $tsfe */
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            $pageId,
            ''
        );
        // We do not want (nor need) EXT:realurl to be invoked:
        //$GLOBALS['TSFE']->checkAlternativeIdMethods();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->getConfigArray();
        if ($pageId > 0) {
            $GLOBALS['TSFE']->settingLanguage();
        }
        Locales::setSystemLocaleFromSiteLanguage($GLOBALS['TSFE']->getLanguage());
    }
}

/**
 * @var $searchResults \Visol\Solrmultilangresults\Eid\SearchResults
 */
$searchResults = GeneralUtility::makeInstance(SearchResults::class);

// Get and JSONify the number of results for a query
$data = json_encode(['numberOfResults' => $searchResults->getNumberOfResults()]);
header('Expires: Wed, 23 Nov 1983 18:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Length: ' . strlen($data));
header('Content-Type: application/json; charset=utf-8');
header('Content-Transfer-Encoding: 8bit');
echo($data);
