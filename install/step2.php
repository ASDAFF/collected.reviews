<?IncludeModuleLangFile(__FILE__);?>

<?
echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
?>

<p><?=GetMessage("GO_TO_COLLECTED_ART_SITE");?><a target="_blank" href="http://asdaff.github.io/marketplace/reviews/?from=install">http://asdaff.github.io/marketplace/reviews/</a></p>
<?if(array_key_exists("install_demo", $_REQUEST) && $_REQUEST["install_demo"] == 'Y'):?>
<p><?=GetMessage("GO_TODEMO_SECTION")?>: <a target="_blank" href="/reviews_demo/">/reviews_demo/</a></p>
<?endif;?>
<br/>
<p style="color:red;"><?=GetMessage("FREE_SUPPORT");?></p>
<br/>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
</form>
