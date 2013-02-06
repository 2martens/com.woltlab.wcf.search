<div class="container marginTop">
	<ul class="containerList messageSearchResultList">
		{foreach from=$objects item=message}
			<li>
				<div class="box48">
					<a href="{link controller='User' object=$message->getUserProfile()}{/link}" title="{$message->getUserProfile()->username}" class="framed">{@$message->getUserProfile()->getAvatar()->getImageTag(48)}</a>
					
					<div>
						<hgroup class="containerHeadline">
							<h1><a href="{$message->getLink($query)}">{@$message->getSubject()}</a></h1>
							<h2>
								<a href="{link controller='User' object=$message->getUserProfile()}{/link}" class="userLink" data-user-id="{@$message->getUserProfile()->userID}">{$message->getUserProfile()->username}</a>
								<small>- {@$message->getTime()|time}</small>
								{if $message->getContainerTitle()}<small>- <a href="{$message->getContainerLink()}">{$message->getContainerTitle()}</a></small>{/if}
							</h2> 
							<h3 class="containerContentType"><small>{lang}wcf.search.object.{@$message->getObjectTypeName()}{/lang}</small></h3>
						</hgroup>
						
						<p>{@$message->getFormattedMessage()}</p>
					</div>
				</div>
			</li>
		{/foreach}
	</ul>
</div>