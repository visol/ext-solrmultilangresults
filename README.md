Solr Multi Language Results Hints
==========================================

This extension displays a hint about search results in other languages of a website and provides a link to the search in the according language.

![Screenshot of frontend](https://raw.github.com/visol/ext-solrmultilangresults/master/Documentation/frontend.png)

Installation
------------

The extension was developed for Solr 7.5 and runs on TYPO3 8.7 LTS. It might run on TYPO3 7.6 LTS as well but was not tested with any other TYPO3 or Solr version.

Installation with Composer:

```composer require visol/solrmultilangresults```

* Activate extension
* Add the Static TypoScript file and adapt the configuration if needed.
* Make sure JQuery is installed in your website.
* Embed Resources/Public/JavaScript/SolrMultiLangResults.js to your website. An example can be found in the TypoScript file.
* Make sure that a container ```<div class="solrmultilangresults-container"></div>``` is part of the page where you use the plugin. A good idea is to place it in the EXT:solr results template.
* Add the plugin on the site that contains the Solr search plugin.

Configuration
----------
	plugin.tx_solrmultilangresults {
		settings {
			# ISO code (flag) of the default language, this is used to fetch the correct localized label
			defaultLanguageFlag = de
			# Title of the default language, this is used as fallback if no localized label is available
			defaultLanguageTitle = Deutsch
			# Languages to be excluded
			#excludedLanguages = 2
		}
	}

Credits
--------

Developed by visol digitale Dienstleistungen GmbH, www.visol.ch

Pull requests and improvements are welcome!
