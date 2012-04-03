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
		
	init: function(searchArea) {
		this._searchArea = searchArea;
		
		new WCF.Search.Message.KeywordList(this._searchArea.find('input'), $.proxy(this._callback, this));
	},

	_callback: function(data) {
		this._searchArea.find('input').val(data.label);
		this._searchArea.find('input').focus();
		return false;
	}
};