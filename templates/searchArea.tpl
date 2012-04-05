<aside id="search" class="searchBar">
	<form method="post" action="{link controller='Search'}{/link}">
		<input type="search" name="q" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" value="" />
	</form>
</aside>

<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/WCF.Search.Message.js"></script>
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Search.Message.SearchArea($('#search'));
	});
	//]]>
</script>