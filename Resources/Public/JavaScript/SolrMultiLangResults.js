window.SolrMultiLangResultsConfig = window.SolrMultiLangResultsConfig || {};
SolrMultiLangResultsConfig.queue = [];
SolrMultiLangResultsConfig.showResultsHints = false;

var SolrMultiLangResults = {
	/**
	 * Get results hint for a search language
	 *
	 * @param $targetObject
	 */
	getResultsHint: function ($targetObject) {
		var searchPageUid = $('.solr-multilang-results-hints').data('searchpageuid');
		var queryUri = $targetObject.data('queryuri');
		var languageUid = $targetObject.data('language');
		var queryString = $('.tx-solr-q', '#tx-solr-search').val();
		var requestUri = queryUri + '&q=' + queryString;
		var request = $.ajax({
			type: "GET",
			url: requestUri,
			success: function (data) {
				var numberOfResults = data.numberOfResults;
				if (numberOfResults > 0) {
					var $targetElement = $('.result-hint[data-language=' + languageUid + ']');
					$targetElement.find('.resultsCount').html(numberOfResults);
					if (numberOfResults == 1) {
						// Hide plural "results" label
						$targetElement.find('.label-results').hide();
					} else {
						// Hide singular "result" label
						$targetElement.find('.label-result').hide();
					}
					var $targetLink = $targetElement.find('a');
					var linkToSearch = $targetLink.attr('href') + '?id=' + searchPageUid + '&q=' + queryString;
					$targetLink.attr('href', linkToSearch);
					$targetElement.slideDown();
					SolrMultiLangResultsConfig.showResultsHints = true;
				}
			}
		});
		SolrMultiLangResultsConfig.queue.push(request);
	}
};

$(function () {
	var $resultHintConfigurations = $('.result-hint');
	var queryString = $('.tx-solr-q', '#tx-solr-search').val();
	if (queryString) {
		$.each($resultHintConfigurations, function () {
			SolrMultiLangResults.getResultsHint($(this));
		});
		$.when.apply(null, SolrMultiLangResultsConfig.queue).done(function () {
			if (SolrMultiLangResultsConfig.showResultsHints) {
				var $targetContainer = $('.solrmultilangresults-container');
				$('.solr-multilang-results-hints').detach().appendTo($targetContainer).slideDown();
			}
		});
	}
});