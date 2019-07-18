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
		{fbvElement id="ldapUrl" type="text" name="ldapUrl" value=$ldapUrl label="plugins.generic.ldap.manager.settings.ldapUrl" placeholder="plugins.generic.ldap.manager.settings.ldapUrlPlaceholder"}
		{fbvElement id="ldapSuffixSetting" type="text" name="ldapSuffix" value=$ldapSuffix label="plugins.generic.ldap.manager.settings.ldapSuffix" placeholder="plugins.generic.ldap.manager.settings.ldapSuffixPlaceholder"}
		{fbvElement id="ldapFilterSetting" type="text" name="ldapFilter" value=$ldapFilter label="plugins.generic.ldap.manager.settings.ldapFilter" placeholder="plugins.generic.ldap.manager.settings.ldapFilterPlaceholder"}
	{/fbvFormSection}
	{fbvFormSection label="plugins.generic.ldap.manager.settings.ldapBind"}
		{fbvElement id="ldapBindUserSetting" type="text" name="ldapBindUser" value=$ldapBindUser label="plugins.generic.ldap.manager.settings.ldapBindUser" placeholder="plugins.generic.ldap.manager.settings.ldapBindUserPlaceholder"}
		{fbvElement id="ldapBindPasswordSetting" type="text" password=true name="ldapBindPassword" value=$ldapBindPassword label="plugins.generic.ldap.manager.settings.ldapBindPassword" placeholder="plugins.generic.ldap.manager.settings.ldapBindPasswordPlaceholder"}
	{/fbvFormSection}
	{fbvFormSection label="plugins.generic.ldap.manager.settings.ldapSelfService"}
		{fbvElement id="ldapSelfServiceUrlSetting" type="text" name="ldapSelfServiceUrl" value=$ldapSelfServiceUrl label="plugins.generic.ldap.manager.settings.ldapSelfServiceUrl" placeholder="plugins.generic.ldap.manager.settings.ldapSelfServiceUrlPlaceholder"}
	{/fbvFormSection}
	{fbvFormSection label="plugins.generic.ldap.manager.settings.ldapLocalLoginOrder" list=true}
		{fbvElement type="radio" id="ldapLocalLoginOrderBefore" name="ldapLocalLoginOrder" value="before" checked=$ldapLocalLoginOrder|compare:"before" label="plugins.generic.ldap.manager.settings.ldapLocalLoginOrder.before"}
		{fbvElement type="radio" id="ldapLocalLoginOrderAfter" name="ldapLocalLoginOrder" value="after" checked=$ldapLocalLoginOrder|default:true|compare:"after" label="plugins.generic.ldap.manager.settings.ldapLocalLoginOrder.after"}
		{fbvElement type="radio" id="ldapLocalLoginOrderNone" name="ldapLocalLoginOrder" value="never" checked=$ldapLocalLoginOrder|compare:"none" label="plugins.generic.ldap.manager.settings.ldapLocalLoginOrder.none"}
	{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
