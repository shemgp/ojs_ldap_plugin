{**
 * plugins/generic/ldap/templates/settingsForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Copyright (c) 2019 Shem Pasamba
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Google Analytics plugin settings
 *
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#ldapSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="ldapSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="ldapSettingsFormNotification"}

	<div id="description">{translate key="plugins.generic.ldap.manager.settings.description"}</div>

	{fbvFormArea id="ldapSettingsFormArea"}
	{fbvFormSection label="plugins.generic.ldap.manager.settings.ldapServer"}
		{fbvElement id="ldapUrl" type="text" name="ldapUrl" value=$ldapUrl label="plugins.generic.ldap.manager.settings.ldapUrl"}
		{fbvElement id="ldapSuffixSetting" type="text" name="ldapSuffix" value=$ldapSuffix label="plugins.generic.ldap.manager.settings.ldapSuffix"}
		{fbvElement id="ldapFilterSetting" type="text" name="ldapFilter" value=$ldapFilter label="plugins.generic.ldap.manager.settings.ldapFilter"}
	{/fbvFormSection}
	{fbvFormSection label="plugins.generic.ldap.manager.settings.ldapBind"}
		{fbvElement id="ldapBindUserSetting" type="text" name="ldapBindUser" value=$ldapBindUser label="plugins.generic.ldap.manager.settings.ldapBindUser"}
		{fbvElement id="ldapBindPasswordSetting" type="text" name="ldapBindPassword" value=$ldapBindPassword label="plugins.generic.ldap.manager.settings.ldapBindPassword"}
	{/fbvFormSection}
	{fbvFormSection label="plugins.generic.ldap.manager.settings.ldapSelfService"}
		{fbvElement id="ldapSelfServiceUrlSetting" type="text" name="ldapSelfServiceUrl" value=$ldapSelfServiceUrl label="plugins.generic.ldap.manager.settings.ldapSelfServiceUrl"}
	{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
