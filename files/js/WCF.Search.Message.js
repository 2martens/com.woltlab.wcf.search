/**
 * Namespace
 */
WCF.Search.Message = {};

/**
 * Provides quick search for search keywords.
 * 
 * @see	WCF.Search.Base
 */
WCF.Search.Message.KeywordList = WCF.Search.Base.extend({
	/**
	 * @see	WCF.Search.Base._className
	 */
	_className: 'wcf\\data\\search\\keyword\\SearchKeywordAction'
});

/**
 * 
 */
WCF.Search.Message.SearchArea = function(searchArea) { this.init(searchArea); };
WCF.Search.Message.SearchArea.prototype = {
	_searchArea: null,
	_inputDimensions: null,
		
	init: function(searchArea) {
		this._searchArea = searchArea;
		
		// get dimensions of the input field
		this._inputDimensions = this._searchArea.find('input').getDimensions();
		
		// set default values
		this._searchArea.css('right', ((this._inputDimensions.width + 50) * -1) + 'px');
		
		// set events
		this._searchArea.find('img').click($.proxy(function() {
			if (this._searchArea.css('right') == '-40px') {
				this._searchArea.animate({
					right: '-='+(this._inputDimensions.width + 10)
				});
			}
			else {
				this._searchArea.animate({
					right: '+='+(this._inputDimensions.width + 10)
				}, 100, $.proxy(function() {
					this._searchArea.find('input').focus();
				}, this));
			}
		}, this));
		
		new WCF.Search.Message.KeywordList(this._searchArea.find('input'), $.proxy(this._callback, this));
	},

	_callback: function(data) {
		this._searchArea.find('input').val(data.label);
		this._searchArea.find('input').focus();
		return false;
	}
};