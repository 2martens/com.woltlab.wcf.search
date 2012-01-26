{include file='documentHeader'}

<head>
	<title>{lang}wcf.search.results{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
</head>

<body id="tpl{$templateName|ucfirst}">
{include file='header' sandbox=false}

<header class="mainHeading">
	<img src="{icon size='L'}search1{/icon}" alt="" />
	<hgroup>
		<h1>{if $query}<a href="{link controller='Search'}q={$query|urlencode}{/link}">{lang}wcf.search.results{/lang}</a>{else}{lang}wcf.search.results{/lang}{/if}</h1>
		<h2>{lang}wcf.search.results.description{/lang}</h2>
	</hgroup>
</header>
	
<div class="contentHeader">
	{assign var=encodedHighlight value=$highlight|urlencode}
	{pages print=true assign=pagesLinks controller='SearchResult' id=$searchID link="pageNo=%d&highlight=$encodedHighlight"}
	
	{hascontent}
		<nav>
			<ul class="largeButtons">
				{content}
					{if $alterable}
						<li><a href="{link controller='Search'}modify={@$searchID}{/link}" class"button"><img src="{icon size='M'}search1{/icon}" alt="" /> <span>{lang}wcf.search.results.change{/lang}</span></a></li></ul>
					{/if}
					{event name='largeButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>
	
{assign var=i value=0}
{assign var=length value=$messages|count}
	
{foreach from=$messages item=item}
	{include file=$objectTypes[$item.type]->getResultTemplateName()}
	{assign var=i value=$i+1}
	{assign var=startIndex value=$startIndex+1}
{/foreach}
	
<div class="contentFooter">
	{@$pagesLinks}
	
	{hascontent}
		<nav>
			<ul class="largeButtons">
				{content}
					{if $alterable}
						<li><a href="{link controller='Search'}modify={@$searchID}{/link}" class"button"><img src="{icon size='M'}search1{/icon}" alt="" /> <span>{lang}wcf.search.results.change{/lang}</span></a></li></ul>
					{/if}
					{event name='largeButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>	
	
{*if $alterable}
	<div class="border infoBox">
		<div class="container-1">
			<div class="containerIcon"><img src="{icon}sortM.png{/icon}" alt="" /> </div>
			<div class="containerContent">
				<h3>{lang}wcf.search.results.display{/lang}</h3>
				<form method="post" action="index.php">
					
					<div class="floatContainer">
						<input type="hidden" name="form" value="Search" />
						<input type="hidden" name="searchID" value="{@$searchID}" />
						<input type="hidden" name="pageNo" value="{@$pageNo}" />
						<input type="hidden" name="highlight" value="{$highlight}" />
						
						<div class="floatedElement">
							<label for="sortField">{lang}wcf.search.sortBy{/lang}</label>
							<select id="sortField" name="sortField">
								<option value="relevance"{if $sortField == 'relevance'} selected="selected"{/if}>{lang}wcf.search.sortBy.relevance{/lang}</option>
								<option value="subject"{if $sortField == 'subject'} selected="selected"{/if}>{lang}wcf.search.sortBy.subject{/lang}</option>
								<option value="time"{if $sortField == 'time'} selected="selected"{/if}>{lang}wcf.search.sortBy.creationDate{/lang}</option>
								<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.search.sortBy.author{/lang}</option>
							</select>
						
							<select name="sortOrder">
								<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
								<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
							</select>
						</div>
						
						<div class="floatedElement">
						{if $additionalDisplayOptions|isset}{@$additionalDisplayOptions}{/if}						
						</div>
						<div class="floatedElement">
							<input type="image" class="inputImage" src="{icon}submitS.png{/icon}" alt="{lang}wcf.global.button.submit{/lang}" />
						</div>

						<input type="hidden" name="modify" value="1" />
						{@SID_INPUT_TAG}
					</div>
				</form>
			</div>
		</div>
		
		{if $additionalBoxes|isset}{@$additionalBoxes}{/if}
	</div>
{/if*}

{include file='footer' sandbox=false}
</body>
</html>