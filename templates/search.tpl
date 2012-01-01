{include file='documentHeader'}

<head>
	<title>{lang}wcf.search.title{/lang} - {PAGE_TITLE|language}</title>

	{include file='headInclude' sandbox=false}
</head>

<body id="tpl{$templateName|ucfirst}">
{include file='header' sandbox=false}

<header class="mainHeading">
	<img src="{icon size='L'}search1{/icon}" alt="" />
	<hgroup>
		<h1>{lang}wcf.search.title{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $errorMessage|isset}
	<p class="error">{@$errorMessage}</p>
{/if}

<form method="post" action="{link controller='Search'}{/link}">
	<div class="border content">
		<fieldset>
			<legend>{lang}wcf.search.general{/lang}</legend>
	
			<dl{if $errorField == 'q'} class="formError"{/if}>
				<dt><label for="searchTerm">{lang}wcf.search.query{/lang}</label></dt>
				<dd>
					<input type="text" id="searchTerm" name="q" value="{$query}" class="long" maxlength="255" />
					<label><input type="checkbox" name="subjectOnly" value="1"{if $subjectOnly == 1} checked="checked"{/if} /> {lang}wcf.search.subjectOnly{/lang}</label>
					<small>{lang}wcf.search.query.description{/lang}</small>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="searchAuthor">{lang}wcf.search.author{/lang}</label></dt>
				<dd>
					<input type="text" id="searchAuthor" name="username" value="{$username}" class="long" maxlength="255" />
					<label><input type="checkbox" name="nameExactly" value="1"{if $nameExactly == 1} checked="checked"{/if} /> {lang}wcf.search.matchesExactly{/lang}</label>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="startDate">{lang}wcf.search.startDate{/lang}</label></dt>
				<dd>
					<input type="date" id="startDate" name="startDate" value="{$startDate}" />
				</dd>
			</dl>
			
			<dl>
				<dt><label for="endDate">{lang}wcf.search.endDate{/lang}</label></dt>
				<dd>
					<input type="date" id="endDate" name="endDate" value="{$endDate}" />
				</dd>
			</dl>
			
			<dl>
				<dt><label for="sortField">{lang}wcf.search.sortBy{/lang}</label></dt>
				<dd>
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
				</dd>
			</dl>
			
			<dl>
				<dt><label for="endDate">{lang}wcf.search.type{/lang}</label></dt>
				<dd>
					<ul class="formOptions">
					{foreach from=$objectTypes key=objectTypeName item=objectType}
						{if $objectType->isAccessible()}
							<li><label><input id="{@$objectTypeName}" type="checkbox" name="types[]" value="{@$objectTypeName}"{if $objectTypeName|in_array:$selectedObjectTypes} checked="checked"{/if} {if $objectType->getFormTemplateName()}onclick="showSearchForm('{@$objectTypeName}Form', this.checked)" {/if}/> {lang}wcf.search.type.{@$objectTypeName}{/lang}</label></li>
						{/if}
					{/foreach}
					</ul>
				</dd>
			</dl>
		</fieldset>
		
		{if $useCaptcha}{include file='recaptcha'}{/if}
		
		{foreach from=$objectTypes key=objectTypeName item=objectType}
			{if $objectType->isAccessible() && $objectType->getFormTemplateName()}
				<fieldset id="{$objectTypeName}Form">
					<legend>{lang}wcf.search.type.{$objectTypeName}{/lang}</legend>
					
					<div>{include file=$objectType->getFormTemplateName()}</div>
				
					{if !$objectTypeName|in_array:$selectedObjectTypes}
						<script type="text/javascript">
							//<![CDATA[
							showSearchForm('{$objectTypeName}Form', false);
							//]]>
						</script>
					{/if}
				</fieldset>
			{/if}
		{/foreach}
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer' sandbox=false}

</body>
</html>