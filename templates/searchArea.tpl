<aside class="search" id="search">
	<img src="{icon}search2{/icon}" alt="" style="width: 48px; height: 48px;" title="{lang}wcf.global.button.search{/lang}" class="balloonTooltip" />
	<div>
		
		<form method="post" action="{link controller='Search'}{/link}">
			<input type="search" name="q" results="5" autosave="autosave" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" value="" />
			<!-- Search Settings should go here and be put on an icon (image button?) -->
		</form>
	</div>
</aside>

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		// get input size
		var dimensions = $('#search').find('input').getDimensions();
		$('#search').css('right', ((dimensions.width + 62) * -1) + 'px');
		
		$('#search').find('img').click(function() {
			if ($('#search').css('right') == '-30px') {
				$('#search').animate({
					right: '-='+(dimensions.width + 32)
				});
				$('#search').find('img').addClass('balloonTooltip');
			}
			else {
				$('#search').animate({
					right: '+='+(dimensions.width + 32)
				});
				$('#search').find('img').removeClass('balloonTooltip');
			}
		});
	});
	//]]>
</script>