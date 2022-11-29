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

use ApacheSolrForTypo3\Solr\Domain\Search\Query\QueryBuilder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSetService;
use ApacheSolrForTypo3\Solr\Search;
use ApacheSolrForTypo3\Solr\Util;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\Query;

class SearchResultsEid
{

    protected ?Query $query = null;

    private ResponseFactoryInterface $responseFactory;

    protected QueryBuilder $queryBuilder;

    public function __construct(ResponseFactoryInterface $responseFactory, QueryBuilder $queryBuilder)
    {
        $this->responseFactory = $responseFactory;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @var SearchResultSetService
     */
    protected $searcher;

    public function main(ServerRequestInterface $request): ResponseInterface
    {
        $pageUid = array_key_exists('id', $request->getQueryParams()) ? (int)$request->getQueryParams()['id'] : null;
        if (!$pageUid) {
            $result = ['message' => 'Page ID is missing.'];
            $response = $this->getJsonResponse();
            $response->getBody()->write(json_encode($result));
            return $response->withStatus(500);
        }

        $languageId = array_key_exists('L', $request->getQueryParams()) ? (int)$request->getQueryParams()['L'] : 0;

        $result = ['numberOfResults' => $this->getNumberOfResults($pageUid, $languageId)];
        $response = $this->getJsonResponse();
        $response->getBody()->write(json_encode($result));

        return $response->withStatus(200);
    }

    /**
     * Return the number of results for a Solr query respecting the allowed sites
     */
    protected function getNumberOfResults(int $pageUid, int $languageId): int
    {
        /** @var ConnectionManager $solrConnection */
        $solrConnection = GeneralUtility::makeInstance(ConnectionManager::class)->getConnectionByPageId(
            $pageUid,
            $languageId
        );

        $search = GeneralUtility::makeInstance(Search::class, $solrConnection);
        /** @var SearchResultSetService $searcher */
        $searcher = GeneralUtility::makeInstance(SearchResultSetService::class, Util::getSolrConfiguration(), $search);
        $searcher->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->searcher = $searcher;

        $q = GeneralUtility::_GP('q');

        $userAccessGroups = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect(
            'frontend.user',
            'groupIds'
        );

        $this->query = $this->queryBuilder
            ->startFrom(new Query)
            ->useSiteHashFromTypoScript($pageUid)
            ->useUserAccessGroups($userAccessGroups)->getQuery();

        $this->query->setQuery($q);
        $this->searcher->getSearch()->search($this->query);
        $response = $this->searcher->getSearch()->getResponse();

        return $response->getParsedData()->response->numFound;
    }

    private function getJsonResponse(): ResponseInterface
    {
        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Pragma', 'no_cache')
            ->withHeader('Expires', 'Wed, 23 Nov 1983 18:00:00 GMT')
            ->withHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->withHeader('Cache-Control', 'no-cache, must-revalidate');
    }

}
