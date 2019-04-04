var tinyHelp = {};
(function($, th_data) {
	tinyHelp = {
		$pluginFilter: $('#plugin-filter'),
		getCard: function() {
			return document.querySelectorAll('.plugin-card-tinyhelp');
		},
		replaceCardBottom: function() {
			var hints = tinyHelp.getCard();
			$(hints).each(function() {
				var hint = this;
				if ('object' === typeof hint && null !== hint) {
					hint.querySelector('.plugin-card-bottom').outerHTML = '<div class="tinyhelp-plugin-card-bottom"><p>' + th_data.bottom_text + '</p></div>';
					var dismissLink = hint.querySelector('.tinyhelp-dismiss');
					dismissLink.parentNode.parentNode.removeChild(dismissLink.parentNode);
					hint.querySelector('.tinyhelp-plugin-card-bottom').appendChild(dismissLink);
				}
			});
		},
		dismiss: function(moduleName, el) {
			console.log($(el).parents('.plugin-card-tinyhelp'));
			document.getElementById('the-list').removeChild($(el).parents('.plugin-card-tinyhelp')[0]);
			$.ajax({
				url: th_data.base_rest_url,
				method: 'post',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', th_data.nonce);
				},
				data: JSON.stringify({
					module: moduleName,
				}),
				contentType: 'application/json',
				dataType: 'json',
			});
		},
		replaceOnNewResults: function(mutationsList) {
			mutationsList.forEach(function(mutation) {
				if (
					'childList' === mutation.type &&
					1 === document.querySelectorAll('.plugin-card-tinyhelp').length
				) {
					tinyHelp.replaceCardBottom();
				}
			});
		},
		init: function() {
			if (tinyHelp.$pluginFilter.length < 1) {
				return;
			}
			tinyHelp.replaceCardBottom();
			var resultsObserver = new MutationObserver(tinyHelp.replaceOnNewResults);
			resultsObserver.observe(document.getElementById('plugin-filter'), {
				childList: true
			});
			tinyHelp.$pluginFilter
				.on('click', '.tinyhelp-dismiss', function(event) {
					event.preventDefault();
					tinyHelp.dismiss($(this).data('module'), this);
				})

		},
	}
	tinyHelp.init();
})(jQuery, TinyHelp_Data);
