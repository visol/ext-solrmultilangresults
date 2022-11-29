<?php

namespace Visol\Solrmultilangresults\Controller;

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

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ResultsController extends ActionController
{

    /**
     * A plugin that renders a list of links to search results in other languages
     * The results are invisible by default and populated and shown by JavaScript
     */
    public function indexAction(): ResponseInterface
    {
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        $currentLanguage = $languageAspect->getId();

        /** @var Site $site */
        $site = $this->request->getAttribute('site');
        $systemLanguages = $site->getAllLanguages();

        $includedLanguages = [];
        /** @var SiteLanguage $systemLanguage */
        foreach ($systemLanguages as $systemLanguage) {
            if ($systemLanguage->getLanguageId() === $currentLanguage) {
                // We ignore the current language
                continue;
            }
            if (in_array($systemLanguage->getLanguageId(), explode(',', $this->settings['excludedLanguages']))) {
                // We ignore excluded languages
                continue;
            }
            $includedLanguages[] = $this->getDataForLanguage($systemLanguage);
        }

        $this->view->assign('includedLanguages', $includedLanguages);
        $this->view->assign('currentPageId', (int)$GLOBALS['TSFE']->id);
        return $this->htmlResponse();
    }

    /**
     * Gets a localized label for the language if it can be found
     * Fall back to SiteLanguage title of not found
     */
    public function getDataForLanguage(SiteLanguage $siteLanguage): array
    {
        $language = [];
        $language['label'] = LocalizationUtility::translate(
            'language.' . $siteLanguage->getTwoLetterIsoCode(),
            'solrmultilangresults'
        );
        if (!$language['label']) {
            $language['label'] = $siteLanguage->getTitle();
        }
        $language['uid'] = $siteLanguage->getLanguageId();
        return $language;
    }
}
