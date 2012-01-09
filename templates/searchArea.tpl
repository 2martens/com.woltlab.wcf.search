<aside class="search" id="search">
	<img src="{icon}search2{/icon}" alt="" style="width: 48px; height: 48px;" title="{lang}wcf.global.button.search{/lang}" class="balloonTooltip" />
	<div>
		
		<form method="post" action="{link controller='Search'}{/link}">
			<input type="search" name="q" results="5" autosave="autosave" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" value="" />
			<!-- Search Settings should go here and be put on an icon (image button?) -->
		</form>
	</div>
</aside>

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/WCF.Search.Message.js"></script>
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Search.Message.SearchArea($('#search'));
	});
	//]]>
</script>