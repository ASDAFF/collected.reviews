<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

$title = "";
if($arResult["USER_COUNT"] > 0)
{
	$title .= GetMessage("T_REVIEWS_RATING_OT")." ".$arResult["USER_COUNT"]." ";
	$title .= ($arResult["USER_COUNT"] == 1 || ($arResult["USER_COUNT"]-1) % 10 == 0) ? GetMessage("COLLECTED_REVIEWS_RATING_USER") : GetMessage("COLLECTED_REVIEWS_RATING_USERS");
}
?>
<div class="collected_reviews_rating" title="<?=$arResult["RATING"]." ".$title;?>">
	<a href="#" class="star<?if($arParams['RATING_SHORT_TEMPLATE'] == 'Y'):?> short<?endif;?>">
		<span style="width:<?=$arResult["WIDTH"]?>%;"></span>
	</a>
	<div class="users-counter">
		<?=$title;?>
	</div>
</div>
<div style="clear:both;"></div>