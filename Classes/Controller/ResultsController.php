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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
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

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $systemLanguages = $queryBuilder->select('uid')
            ->from('sys_language')
            ->where($queryBuilder->expr()->notIn('uid', $this->settings['excludedLanguages']))
            ->execute()->fetchAllAssociative();

        // We add the default language
        $systemLanguages[] = ['uid' => 0];

        $includedLanguages = [];
        $i = 0;
        foreach ($systemLanguages as $key => $systemLanguage) {
            if ((int)$systemLanguage['uid'] === $currentLanguage) {
                // We ignore the current language
                continue;
            }
            $includedLanguages[$i] = $this->getDataForLanguage($systemLanguage);
            $i++;
        }
        $this->view->assign('includedLanguages', $includedLanguages);
        $this->view->assign('currentPageId', (int)$GLOBALS['TSFE']->id);
        return $this->htmlResponse();
    }

    /**
     * Gets a localized label for the language if it can be found
     * Use configured default language label
     *
     * @param $systemLanguage
     *
     * @return array
     */
    public function getDataForLanguage($systemLanguage): array
    {
        $language = [];
        if ((int)$systemLanguage['uid'] === 0) {
            // The default language
            $systemLanguage['flag'] = $this->settings['defaultLanguageFlag'];
        }
        $languageLabel = LocalizationUtility::translate('language.' . $systemLanguage['flag'], 'solrmultilangresults');
        if (!$languageLabel) {
            if ((int)$systemLanguage['uid'] === 0) {
                $language['label'] = $this->settings['defaultLanguageTitle'];
            } else {
                $language['label'] = $systemLanguage['title'];
            }
        } else {
            $language['label'] = $languageLabel;
        }
        $language['uid'] = (int)$systemLanguage['uid'];
        return $language;
    }
}
