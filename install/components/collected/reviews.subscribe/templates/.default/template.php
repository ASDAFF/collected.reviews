<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<a name="review_subscribe"></a>

<div class="collected_reviews_subscribe_form" id="collected_reviews_subscribe_form">

	<?if (strlen($arResult["MESSAGE"]) > 0):?>
		<b><?=GetMessage("REVIEWS_SUBSCRIBE_TITLE")?></b>
		<b><?=ShowNote($arResult["MESSAGE"])?></b>
		<?if($arParams["NEED_CONFIRM"] == 'Y'):?>
			<?echo GetMessage("REVIEW_SUBSCRIBE_NEED_CONFIRM");?>
		<?endif?>
	<?else:?>
	
	<a class="open" href="#"><?=GetMessage("REVIEWS_SUBSCRIBE_TITLE")?></a>
	
	<form name="review_add" method="POST" action="<?=POST_FORM_ACTION_URI?>#review_subscribe" method="post" enctype="multipart/form-data" data-errors="<?=count($arResult["ERRORS"]);?>">
	
		<?if (count($arResult["ERRORS"])):?>
			<?=ShowError(implode("<br />", $arResult["ERRORS"]))?>
		<?endif;?>
		
		
		
		<?=bitrix_sessid_post()?>

		<div class="inp-area">
			<label>Email <span class="required">*</span></label>
			<input type="text" name="SUBSCRIBE_EMAIL" value="<?=$arResult["EMAIL"];?>"/>
		</div>
		<div class="inp-area">
		<input type="submit" class="update" name="review_subscribe" value="<?=GetMessage("SEND")?>"/>
		</div>
	</form>
	<?endif?>
	
</div>
