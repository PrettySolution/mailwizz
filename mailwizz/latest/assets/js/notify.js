/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
;(function( $, window, document, undefined ){

	var notify = function() {
		
		var messages = {
			error: [],
			warning: [],
			info: [],
			success: [],
		};
		
		var options = {
			container: '#notify-container',
			errorClass: 'alert alert-block alert-danger',
			warningClass: 'alert alert-block alert-warning',
			infoClass: 'alert alert-block alert-info',
			successClass: 'alert alert-block alert-success',
			htmlWrapper: '<div class="{CLASS}">{CONTENT}</div>',
			htmlCloseButton: '<button type="button" class="close" data-dismiss="alert">×</button>',
			htmlHeading: '<p>{CONTENT}</p>',
			errorHeading: '',
			warningHeading: '',
			infoHeading: '',
			successHeading: '',
			
			_merged: false
		};
		
		function getOptions() {
			if (options._merged) {
				return options;
			}
			options._merged = true;
			options = $.extend({}, options, publicAccess.options);
			return options;
		}
		
		function addMessage(message, type) {
			if (typeof messages[type] != 'object') {
				return;
			}
			messages[type].push(message);
		}
		
		function showAllMessages() {
			getOptions();
			var html = [];
			var $container = $(options.container);
			
			if ($container.length == 0) {
				return;
			}
			
			if (messages.error.length > 0) {
				var ul = [];
				ul.push('<ul>');
				for (var i in messages.error) {
				    if (typeof messages.error[i] == 'string') {
					   ul.push('<li>' + messages.error[i] + '</li>');
                    }
				}
				ul.push('</ul>');
				
				var content = [];
				if (options.htmlCloseButton) {
					content.push(options.htmlCloseButton);
				}
				if (options.htmlHeading && options.errorHeading) {
					content.push(options.htmlHeading.replace('{CONTENT}', options.errorHeading));
				}
				content.push(ul.join("\n"));
				
				var _html = options.htmlWrapper.replace('{CLASS}', options.errorClass);
					_html = _html.replace('{CONTENT}', content.join("\n"));
					
				html.push(_html);
			}
			
			if (messages.warning.length > 0) {
				var ul = [];
				ul.push('<ul>');
				for (var i in messages.warning) {
				    if (typeof messages.warning[i] == 'string') {
					   ul.push('<li>' + messages.warning[i] + '</li>');
                    }
				}
				ul.push('</ul>');
				
				var content = [];
				if (options.htmlCloseButton) {
					content.push(options.htmlCloseButton);
				}
				if (options.htmlHeading && options.warningHeading) {
					content.push(options.htmlHeading.replace('{CONTENT}', options.warningHeading));
				}
				content.push(ul.join("\n"));
				
				var _html = options.htmlWrapper.replace('{CLASS}', options.warningClass);
					_html = _html.replace('{CONTENT}', content.join("\n"));
					
				html.push(_html);
			}
			
			if (messages.info.length > 0) {
				var ul = [];
				ul.push('<ul>');
				for (var i in messages.info) {
                    if (typeof messages.info[i] == 'string') {
					   ul.push('<li>' + messages.info[i] + '</li>');
                    }
				}
				ul.push('</ul>');
				
				var content = [];
				if (options.htmlCloseButton) {
					content.push(options.htmlCloseButton);
				}
				if (options.htmlHeading && options.infoHeading) {
					content.push(options.htmlHeading.replace('{CONTENT}', options.infoHeading));
				}
				content.push(ul.join("\n"));
				
				var _html = options.htmlWrapper.replace('{CLASS}', options.infoClass);
					_html = _html.replace('{CONTENT}', content.join("\n"));
					
				html.push(_html);
			}
			
			if (messages.success.length > 0) {
				var ul = [];
				ul.push('<ul>');
				for (var i in messages.success) {
				    if (typeof messages.success[i] == 'string') {
					   ul.push('<li>' + messages.success[i] + '</li>');
                    }
				}
				ul.push('</ul>');
				
				var content = [];
				if (options.htmlCloseButton) {
					content.push(options.htmlCloseButton);
				}
				if (options.htmlHeading && options.successHeading) {
					content.push(options.htmlHeading.replace('{CONTENT}', options.successHeading));
				}
				content.push(ul.join("\n"));
				
				var _html = options.htmlWrapper.replace('{CLASS}', options.successClass);
					_html = _html.replace('{CONTENT}', content.join("\n"));
					
				html.push(_html);
			}
			
			if (html.length == 0) {
				return;
			}
			
			$container.html(html.join(""));
		}
		
		var publicAccess = {
			options: {},
			setOption: function(optionName, optionValue) {
				if (options.hasOwnProperty(optionName)) {
					options[optionName] = optionValue;
				}	
				return this;
			},
			getOption: function(optionName) {
				if (options.hasOwnProperty(optionName)) {
					return options[optionName];
				}
				return false;
			},
			addError: function(message) {
				addMessage(message, 'error');
				return this;
			},
			addWarning: function(message) {
				addMessage(message, 'warning');
				return this;
			},
			addInfo: function(message) {
				addMessage(message, 'info');
				return this;
			},
			addSuccess: function(message) {
				addMessage(message, 'success');
				return this;
			},
			show: function() {
				showAllMessages();
				return this;
			},
			remove: function() {
				for (var i in messages) {
					messages[i] = [];
				}
				$(options.container).empty();
				return this;
			}
		};
	
		return publicAccess;
	}

	window.notify = new notify();

})( jQuery, window, document );