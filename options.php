<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
IncludeModuleLangFile(__FILE__);
	


$module_id = 'collected.reviews';
global $USER;

$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
	
$TAGSMANAGER_DEFAULT_PERMISSION = 'D';


	//if (!$USER->CanDoOperation($module_id))
	//$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
	if ($MODULE_RIGHT >='R'):

		//==========================
		// Табы
		//==========================

		$aTabs = array(
			array(
				'DIV' => 'tab1', 
				'TAB' => GetMessage('COLLECTED_REVIEW_OPT_TAB_MAIN'),
				'ICON' => 'COLLECTED_REVIEW_settings',
				'TITLE' => GetMessage('COLLECTED_REVIEW_OPT_TAB_MAIN_TITLE')
				),
			);

		$aTabs[] = array(
				'DIV' => 'tab2', 
				'TAB' => GetMessage('COLLECTED_REVIEW_OPT_TAB_NOTICE'),
				'ICON' => 'COLLECTED_REVIEW_notice',
				'TITLE' => GetMessage('COLLECTED_REVIEW_OPT_TAB_NOTICE_TITLE')
			);
		
		$aTabs[] = array(
				'DIV' => 'tab3', 
				'TAB' => GetMessage('COLLECTED_REVIEW_OPT_TAB_RIGHTS'),
				'ICON' => 'COLLECTED_REVIEW_RIGHT',
				'TITLE' => GetMessage('COLLECTED_REVIEW_OPT_TAB_RIGHTS_TITLE')
			);

		$tabControl = new CAdminTabControl('tabControl', $aTabs);
		
		//==========================
		// суб Табы
		//==========================
		$arSites = array();
		$aSubTabs = array();
		$aSubTabs2 = array();
		
		$dbSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
		while ($site = $dbSites->Fetch())
		{
			$site["ID"] = htmlspecialchars($site["ID"]);
			$site["NAME"] = htmlspecialchars($site["NAME"]);
			$arSites[] = $site;

			$aSubTabs[] = array("DIV" => "opt_main_".$site["ID"], "TAB" => $site["NAME"].' ['.$site["ID"].']', 'TITLE' => '');
			$aSubTabs2[] = array("DIV" => "opt_notice_".$site["ID"], "TAB" => $site["NAME"].' ['.$site["ID"].']', 'TITLE' => "");
		}
		
		$subTabControl = new CAdminViewTabControl("subTabControl", $aSubTabs);
		$subTabControl2 = new CAdminViewTabControl("subTabControl2", $aSubTabs2);
		
		if($REQUEST_METHOD=='POST' && strlen($Update.$Apply.$RestoreDefaults)>0 && $MODULE_RIGHT >= 'W' && check_bitrix_sessid())
		{
			if(strlen($RestoreDefaults)>0)
			{
				COption::RemoveOption($module_id);
				$APPLICATION->DelGroupRight($module_id);
				
				/*$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
				while($zr = $z->Fetch())
					$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
				*/
			}
			else
			{	
				//save for sites
				foreach($arSites as $site)
				{
					//main setting
					$moderation[$site["LID"]] = $moderation[$site["LID"]] == 'Y' ? 'Y' : 'N' ;
					$subscribe[$site["LID"]] = $subscribe[$site["LID"]] == 'Y' ? 'Y' : 'N' ;
					$subscribe_confirm[$site["LID"]] = $subscribe_confirm[$site["LID"]] == 'Y' ? 'Y' : 'N' ;
					$subscribe_confirm_save[$site["LID"]] = intval($subscribe_confirm_save[$site["LID"]]);
					

					$subscribe_edit_page[$site["LID"]] = htmlspecialchars($subscribe_edit_page[$site["LID"]]);

					$block_ip[$site["LID"]] = $block_ip[$site["LID"]] == 'Y' ? 'Y' : 'N' ;
					$block_ip_maxcount[$site["LID"]] = intval($block_ip_maxcount[$site["LID"]]);
					$block_ip_maxtime[$site["LID"]] = intval($block_ip_maxtime[$site["LID"]]);
					$block_ip_blocktime[$site["LID"]] = intval($block_ip_blocktime[$site["LID"]]);
					
					$block_ip_message[$site["LID"]] = htmlspecialcharsEx($block_ip_message[$site["LID"]]);
					
					$iblock_prop_rating[$site["LID"]] = htmlspecialcharsEx($iblock_prop_rating[$site["LID"]]);
					

					COption::SetOptionString($module_id, 'moderation', $moderation[$site["LID"]], '', $site["LID"]);
					COption::SetOptionString($module_id, 'subscribe', $subscribe[$site["LID"]], '', $site["LID"]);
					COption::SetOptionString($module_id, 'subscribe_confirm', $subscribe_confirm[$site["LID"]], '', $site["LID"]);
					COption::SetOptionInt($module_id, 'subscribe_confirm_save', $subscribe_confirm_save[$site["LID"]], '', $site["LID"]);
					COption::SetOptionString($module_id, 'subscribe_edit_page', $subscribe_edit_page[$site["LID"]], '', $site["LID"]);
					
					COption::SetOptionString($module_id, 'block_ip', $block_ip[$site["LID"]], '', $site["LID"]);
					COption::SetOptionInt($module_id, 'block_ip_maxcount', $block_ip_maxcount[$site["LID"]], '', $site["LID"]);
					COption::SetOptionInt($module_id, 'block_ip_maxtime', $block_ip_maxtime[$site["LID"]], '', $site["LID"]);
					COption::SetOptionString($module_id, 'block_ip_message', $block_ip_message[$site["LID"]], '', $site["LID"]);

					COption::SetOptionString($module_id, 'iblock_prop_rating', $iblock_prop_rating[$site["LID"]], '', $site["LID"]);

					
					//COption::SetOptionString($module_id, 'html', $html[$site["LID"]], '', $site["LID"]);
					//$form_message_success[$site["LID"]] = htmlspecialcharsEx($form_message_success[$site["LID"]]);
					//COption::SetOptionString($module_id, 'form_message_success', $form_message_success[$site["LID"]], 'message when successfully added', $site["LID"]);

					
					//notice
					COption::SetOptionString($module_id, 'notice_email', trim($notice_email[$site["LID"]]), '', $site["LID"]);
				}
				
				//ob_start();
				$Update = $_POST["Update"].$_POST["Apply"];
				//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
				//ob_end_clean();
				
				if($strError=="")
				{
					/*if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
						LocalRedirect($_REQUEST["back_url_settings"]);
					else
						LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
					*/
				}
			}
		}
		
		$ONE_SITE = count($arSites) == 1 ? true : false ;
		
		/*
		if(count($strError) > 0)
		{
			$e = new CAdminException($strError);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			$message = new CAdminMessage(GetMessage("FORM_ERROR_SAVE"), $e);
			echo $message->Show();
		}
		*/
		?>
		
		
		<?
		if (!function_exists('curl_init'))
			ShowError(GetMessage('COLLECTED_REVIEW_CURLERROR'));
		?>


		<?$tabControl->Begin();?>
		
		<form method='post' action='<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>'>
		<?$tabControl->BeginNextTab();?>

			<?if(!$ONE_SITE):?>
			<tr>
				<td colspan="2">
				<?endif;?>
					<?
					if(!$ONE_SITE) 
						$subTabControl->Begin();
					
					foreach ($arSites as $site)
					{
						if(!$ONE_SITE) 
							$subTabControl->BeginNextTab();
						?>
						<?if(!$ONE_SITE):?><table width="75%" align="center"><?endif;?>
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_MODERATION") ?></td>
								<td>
									<?
									$moderation = COption::GetOptionString($module_id, 'moderation', '', $site["LID"]);
									?>
									<input type="checkbox" name="moderation[<?=$site["LID"]?>]" value="Y" <?if($moderation == 'Y'):?>checked<?endif;?>/>
								</td>
							</tr>
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_SUBSCRIBE")?></td>
								<td>
									<?
									$subscribe = COption::GetOptionString($module_id, 'subscribe', '', $site["LID"]);
									?>
									<input type="checkbox" name="subscribe[<?=$site["LID"]?>]" value="Y" <?if($subscribe == 'Y'):?>checked<?endif;?>/>
								</td>
							</tr>
							
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_SUBSCRIBE_CONFIRM")?></td>
								<td>
									<?
									$subscribe_confirm = COption::GetOptionString($module_id, 'subscribe_confirm', '', $site["LID"]);
									?>
									<input type="checkbox" name="subscribe_confirm[<?=$site["LID"]?>]" value="Y" <?if($subscribe_confirm == 'Y'):?>checked<?endif;?>/>
								</td>
							</tr>
							
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_SUBSCRIBE_CONFIRM_SAVE")?></td>
								<td>
									<?
									$subscribe_confirm_save = COption::GetOptionInt($module_id, 'subscribe_confirm_save', '', $site["LID"]);
									?>
									<input type="text" name="subscribe_confirm_save[<?=$site["LID"]?>]" size="3" value="<?=$subscribe_confirm_save;?>"/>
								</td>
							</tr>
							
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_SUBSCRIBE_EDIT_PAGE");?></td>
								<td class="adm-detail-content-cell-r">
									<?
									$subscribe_edit_page = COption::GetOptionString($module_id, 'subscribe_edit_page', '', $site["LID"]);
									?>
									<input type="text" size="50" name="subscribe_edit_page[<?=$site["LID"]?>]" value="<?=$subscribe_edit_page?>"/>
								</td>
							</tr>
							
							
							<tr class='heading'>
								<td align='center' colspan='2' nowrap><?=GetMessage('COLLECTED_REVIEW_PROTECTION')?></td>
							</tr>
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_PROTECTION_BLOCK_IP")?></td>
								<td>
									<?
									$block_ip = COption::GetOptionString($module_id, 'block_ip', '', $site["LID"]);
									?>
									<input type="checkbox" id="block_ip_<?=$site["LID"]?>" name="block_ip[<?=$site["LID"]?>]" value="Y" <?if($block_ip == 'Y'):?>checked<?endif;?>/>
									<script>
										BX.ready(function(){
											BX.bind(BX("block_ip_<?=$site["LID"]?>"), "change", function(){
												if(this.checked) {
													BX("tr_block_ip_maxcount_<?=$site["LID"]?>").style.display = '';
													BX("tr_block_ip_maxtime_<?=$site["LID"]?>").style.display = '';
													BX("tr_block_ip_blocktime_<?=$site["LID"]?>").style.display = '';
													BX("tr_block_ip_message_<?=$site["LID"]?>").style.display = '';
													}
												else {
													BX("tr_block_ip_maxcount_<?=$site["LID"]?>").style.display = 'none';
													BX("tr_block_ip_maxtime_<?=$site["LID"]?>").style.display = 'none';
													BX("tr_block_ip_blocktime_<?=$site["LID"]?>").style.display = 'none';
													BX("tr_block_ip_message_<?=$site["LID"]?>").style.display = 'none';
													}
												});
											});
									</script>
								</td>
							</tr> 
							<tr id="tr_block_ip_maxcount_<?=$site["LID"]?>" <?if($block_ip == "N"):?>style="display:none;"<?endif;?>>
								<td><?=GetMessage("COLLECTED_REVIEW_PROTECTION_BLOCK_IP_MAX");?></td>
								<td class="adm-detail-content-cell-r">
									<?
									$block_ip_maxcount = COption::GetOptionInt($module_id, 'block_ip_maxcount', '', $site["LID"]);
									?>
									<input type="text" size="5" name="block_ip_maxcount[<?=$site["LID"]?>]" value="<?=$block_ip_maxcount?>"/>
								</td>
							</tr>
							<tr id="tr_block_ip_maxtime_<?=$site["LID"]?>" <?if($block_ip == "N"):?>style="display:none;"<?endif;?>>
								<td><?=GetMessage("COLLECTED_REVIEW_PROTECTION_BLOCK_IP_MAXTIME");?></td>
								<td class="adm-detail-content-cell-r">
									<?
									$block_ip_maxtime = COption::GetOptionInt($module_id, 'block_ip_maxtime', '', $site["LID"]);
									?>
									<input type="text" size="5" name="block_ip_maxtime[<?=$site["LID"]?>]" value="<?=$block_ip_maxtime?>"/>
								</td>
							</tr>
							
							<tr id="tr_block_ip_blocktime_<?=$site["LID"]?>" <?if($block_ip == "N"):?>style="display:none;"<?endif;?>>
								<td><?=GetMessage("COLLECTED_REVIEW_PROTECTION_BLOCK_IP_BLOCKTIME");?></td>
								<td class="adm-detail-content-cell-r">
									<?
									$block_ip_blocktime = COption::GetOptionInt($module_id, 'block_ip_blocktime', '', $site["LID"]);
									?>
									<input type="text" size="5" name="block_ip_blocktime[<?=$site["LID"]?>]" value="<?=$block_ip_blocktime?>"/>
								</td>
							</tr>
							
							<tr id="tr_block_ip_message_<?=$site["LID"]?>" <?if($block_ip == "N"):?>style="display:none;"<?endif;?>>
								<td><?=GetMessage("COLLECTED_REVIEW_PROTECTION_BLOCK_IP_MESSAGE");?></td>
								<td class="adm-detail-content-cell-r">
									<?
									$block_ip_message = COption::GetOptionInt($module_id, 'block_ip_message', '', $site["LID"]);
									?>
									<textarea rows="5" cols="50" name="block_ip_message[<?=$site["LID"]?>]"><?=$block_ip_message?></textarea>
								</td>
							</tr>
							
							
							
							
							
							<tr class='heading'>
								<td align='center' colspan='2' nowrap><?=GetMessage('COLLECTED_REVIEW_IBLOCK')?></td>
							</tr>
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_IBLOCK_PROP_RATING");?></td>
								<td class="adm-detail-content-cell-r">
									<?
									$iblock_prop_rating = COption::GetOptionString($module_id, 'iblock_prop_rating', '', $site["LID"]);
									?>
									<input type="text" size="50" name="iblock_prop_rating[<?=$site["LID"]?>]" value="<?=$iblock_prop_rating?>"/>
									<br/>
									<small><?=GetMessage("COLLECTED_REVIEW_IBLOCK_PROP_RATING_INFO");?></small>
								</td>
							</tr>
							
						<?if(!$ONE_SITE):?></table><?endif;?>
						<?
					}
					if(!$ONE_SITE) 
						$subTabControl->End();
					?>
				<?if(!$ONE_SITE):?>
				</td>
			</tr>
			<?endif;?>
		
		<?$tabControl->BeginNextTab();?>
			<?if(!$ONE_SITE):?>
			<tr>
				<td colspan="2">
				<?endif;?>
					<?
					if(!$ONE_SITE) $subTabControl2->Begin();
					foreach ($arSites as $site)
					{
						if(!$ONE_SITE)  $subTabControl2->BeginNextTab();
						?>
						<?if(!$ONE_SITE):?><table width="75%" align="center"><?endif;?>
							
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_EMAIL_MAIN") ?><br/><small><?=GetMessage("COLLECTED_REVIEW_EMAIL_MAIN_INFO") ?></small></td>
								<td>
									<?
									$notice_email = COption::GetOptionString($module_id, 'notice_email', '', $site["LID"]);
									?>
									<input type="text" name="notice_email[<?=$site["LID"]?>]" value="<?=$notice_email?>" placeholder="<?=GetMessage('COLLECTED_REVIEW_EMAIL')?>"/>
								</td>
							</tr>
							
							<tr class='heading'>
								<td align='center' colspan='2' nowrap><?=GetMessage('COLLECTED_REVIEW_MAIL_EVENTS')?></td>
							</tr>
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEW_CREATE")?></td>
								<td>
								<a href="/bitrix/admin/type_edit.php?EVENT_NAME=COLLECTED_REVIEWS_CREATE" target="_blank"><?=GetMessage("COLLECTED_REVIEW_MAIL_EVENT_GO_SETTING")?></a> <small>(COLLECTED_REVIEWS_CREATE)</small>
								</td>
							</tr>
							
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM")?></td>
								<td>
								<a href="/bitrix/admin/type_edit.php?EVENT_NAME=COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM" target="_blank"><?=GetMessage("COLLECTED_REVIEW_MAIL_EVENT_GO_SETTING")?></a> <small>(COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM)</small>
								</td>
							</tr>
							
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEWS_SUBSCRIBE")?></td>
								<td>
								<a href="/bitrix/admin/type_edit.php?EVENT_NAME=COLLECTED_REVIEWS_SUBSCRIBE" target="_blank"><?=GetMessage("COLLECTED_REVIEW_MAIL_EVENT_GO_SETTING")?></a> <small>(COLLECTED_REVIEWS_SUBSCRIBE)</small>
								</td>
							</tr>
							
							<tr>
								<td><?=GetMessage("COLLECTED_REVIEWS_SUBSCRIBE_EDIT")?></td>
								<td>
								<a href="/bitrix/admin/type_edit.php?EVENT_NAME=COLLECTED_REVIEWS_SUBSCRIBE_EDIT" target="_blank"><?=GetMessage("COLLECTED_REVIEW_MAIL_EVENT_GO_SETTING")?></a> <small>(COLLECTED_REVIEWS_SUBSCRIBE_EDIT)</small>
								</td>
							</tr>
							
							
							

						<?if(!$ONE_SITE):?></table><?endif;?>
						<?
					}
					if(!$ONE_SITE) $subTabControl2->End();
					?>
				<?if(!$ONE_SITE):?>	
				</td>
			</tr>
			<?endif;?>
		
	
		<?$tabControl->BeginNextTab();?>
			<?$Update = $_POST["Update"].$_POST["Apply"];?>
			<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>

		<?
		
		if($REQUEST_METHOD=='POST' && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid()) 
		{
			
			if(strlen($Update)>0 && strlen($_REQUEST['back_url_settings'])>0)
				LocalRedirect($_REQUEST['back_url_settings']);
			else
				LocalRedirect($APPLICATION->GetCurPage().'?mid='.urlencode($mid).'&lang='.urlencode(LANGUAGE_ID).'&back_url_settings='.urlencode($_REQUEST['back_url_settings']).'&'.$tabControl->ActiveTabParam());	
		}
		?>

		<?$tabControl->Buttons();?>
			<input <?if ($MODULE_RIGHT<'W') echo 'disabled' ?> type='submit' name='Update' value='<?=GetMessage('MAIN_SAVE')?>' title='<?=GetMessage('MAIN_OPT_SAVE_TITLE')?>'>
			<input <?if ($MODULE_RIGHT<'W') echo 'disabled' ?> type='submit' name='Apply' value='<?=GetMessage('MAIN_OPT_APPLY')?>' title='<?=GetMessage('MAIN_OPT_APPLY_TITLE')?>'>
			<?if(strlen($_REQUEST['back_url_settings'])>0):?>
				<input type='button' name='Cancel' value='<?=GetMessage('MAIN_OPT_CANCEL')?>' title='<?=GetMessage('MAIN_OPT_CANCEL_TITLE')?>' onclick='window.location='<?=htmlspecialchars(CUtil::addslashes($_REQUEST['back_url_settings']))?>''>
				<input type='hidden' name='back_url_settings' value='<?=htmlspecialchars($_REQUEST['back_url_settings'])?>'>
			<?endif?>
			<input <?if ($MODULE_RIGHT<'W') echo 'disabled' ?> type='submit' name='RestoreDefaults' title='<?=GetMessage('MAIN_HINT_RESTORE_DEFAULTS')?>' OnClick='confirm('<?=AddSlashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')' value='<?=GetMessage('MAIN_RESTORE_DEFAULTS')?>'>
			<?=bitrix_sessid_post();?>
		<?$tabControl->End();?>
		</form>
		
		<?echo BeginNote(); ?>
		<p><?=GetMessage("GO_TO_COLLECTED_ART_SITE");?><a target="_blank" href="http://asdaff.github.io/marketplace/reviews/?from=options">http://asdaff.github.io/marketplace/reviews/</a></p>
		<?echo EndNote();?>
		
	<?else:?>
		<?=CAdminMessage::ShowMessage(GetMessage('NO_RIGHTS_FOR_VIEWING'));?>
	<?endif;?>