<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="collected_reviews_unsubscribe_form" id="collected_reviews_subscribe_form">

	<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

	/*<h3><?=GetMessage("REVIEWS_UNSUBSCR_TITLE")?></h3>*/
	?>

	<?if (count($arResult["ERRORS"])):?>
		<?=ShowError(implode("<br />", $arResult["ERRORS"]))?>
	<?endif?>
	<?if (strlen($arResult["MESSAGE"]) > 0):?>
		<b><?=ShowNote($arResult["MESSAGE"])?></b>
	<?else:?>

	<form name="review_add" method="POST" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>
		
		<p><?=GetMessage("REVIEWS_UNSUBSCR_INFO")?></p>
		
		<div class="inp-area">
			<label><?=GetMessage("REVIEWS_UNSUBSCR_EMAIL")?> <span class="required">*</span></label>
			<input type="text" name="SUBSCRIBE_EMAIL" value="<?=$arResult["EMAIL"];?>" placeholder="<?=GetMessage("REVIEWS_UNSUBSCR_EMAIL_PH")?>"/>
			<input type="submit" class="update" name="review_get_mail" value="<?=GetMessage("SUBSCRIBE_EDIT")?>"/>
		</div>		
	</form>
	<?endif?>
</div>
