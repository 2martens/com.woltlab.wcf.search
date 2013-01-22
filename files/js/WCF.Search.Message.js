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
	_className: 'wcf\\data\\search\\keyword\\SearchKeywordAction',
	
	/**
	 * dropdown divider
	 * @var	jQuery
	 */
	_divider: null,
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, excludedSearchValues) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.Search.Message.KeywordList] The given callback is invalid, aborting.");
			return;
		}
		
		this._callback = callback;
		this._excludedSearchValues = [];
		if (excludedSearchValues) {
			this._excludedSearchValues = excludedSearchValues;
		}
		this._searchInput = $(searchInput).keyup($.proxy(this._keyUp, this));
		
		var $dropdownMenu = this._searchInput.next('.dropdownMenu');
		var $lastDivider = $dropdownMenu.find('li.dropdownDivider').last();
		this._divider = $('<li class="dropdownDivider" />').hide().insertBefore($lastDivider);
		this._list = $('<li class="dropdownList" />').hide().insertBefore($lastDivider);
		
		// supress clicks on checkboxes
		$dropdownMenu.find('input, label').on('click', function(event) { event.stopPropagation(); });
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(item) {
		this._divider.show();
		this._list.show();
		
		this._super(item);
	},
	
	/**
	 * @see	WCF.Search.Base._clearList()
	 */
	_clearList: function(clearSearchInput) {
		if (clearSearchInput) {
			this._searchInput.val('');
		}
		
		this._divider.hide();
		this._list.hide().empty();
		
		WCF.CloseOverlayHandler.removeCallback('WCF.Search.Base');
	}
});

/**
 * 
 */
WCF.Search.Message.SearchArea = Class.extend({
	_searchArea: null,
	
	init: function(searchArea) {
		this._searchArea = searchArea;
		
		new WCF.Search.Message.KeywordList(this._searchArea.find('input[type=search]'), $.proxy(this._callback, this));
		
		// forward clicks on the search icon to input field
		var self = this;
		var $input = this._searchArea.find('input[type=search]');
		this._searchArea.click(function() { $input.focus().trigger('click'); return false; });
	},
	
	_callback: function(data) {
		this._searchArea.find('input').val(data.label);
		this._searchArea.find('input').focus();
		return false;
	}
});