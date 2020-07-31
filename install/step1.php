<?IncludeModuleLangFile(__FILE__);?>


<form action="<?=$APPLICATION->GetCurPage()?>" name="form1" id="collected_install">
	<?=bitrix_sessid_post()?>
	
	<input type="hidden" name="id" value="collected.reviews"/>
	<input type="hidden" name="install" value="Y"/>
	<input type="hidden" name="step" value="2"/>
	<input type="hidden" name="lang" value="<?echo LANG?>"/>

	<b class="heading"><?echo GetMessage("COLLECTED_REVIEWS_INSTALL_MAIN")?></b>
	<br/>
	<p>
	<input type="checkbox" name="install_demo" id="collected_install_demo" value="Y" checked />
	<label for="collected_install_demo"><?echo GetMessage("COLLECTED_REVIEWS_INSTALL_DEMO")?>
	<p style="margin-left: 25px;"><small><?echo GetMessage("COLLECTED_REVIEWS_INSTALL_DEMO_DESC")?></small></p>
	</label>
	</p>
	<br/>
	<br/>
	<input type="submit" name="" value="<?echo GetMessage("MOD_INSTALL")?>"/>
</form>
