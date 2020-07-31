<?IncludeModuleLangFile(__FILE__);?>

<form action="<?echo $APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>"/>
	<input type="hidden" name="id" value="collected.reviews"/>
	<input type="hidden" name="uninstall" value="Y"/>
	<input type="hidden" name="step" value="2"/>
	<?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<p><?echo GetMessage("MOD_UNINST_SAVE")?></p>
	<p><input type="checkbox" name="SAVE_DATA" id="collected_mm_save_data" value="Y" checked /><label for="collected_mm_save_data"><?echo GetMessage("MOD_UNINST_SAVE_DATA")?></label></p>
	<p><input type="checkbox" name="SAVE_DEMO" id="collected_mm_save_demo" value="Y" checked /><label for="collected_mm_save_demo"><?echo GetMessage("MOD_UNINST_SAVE_DEMO")?></label></p>
	<p><input type="checkbox" name="SAVE_OPTIONS" id="collected_mm_save_options" value="Y" checked /><label for="collected_mm_save_options"><?echo GetMessage("MOD_UNINST_SAVE_OPTIONS")?></label></p>
	
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>"/>
</form>