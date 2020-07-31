<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

/*
template params

$arParams['SHOW_RATING_TOTAL'] 
$arParams['SHOW_RATING']
$arParams['RATING_SHORT_TEMPLATE']
$arParams['REVIEW_FULL']
*/
?>

<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>

<div class="collected_reviews_list" id="collected_reviews_list_<?=$arResult["LIST_ID"]?>">

	<?if (count($arResult["ERRORS"])):?>
		<?=ShowError(implode("<br />", $arResult["ERRORS"]))?>
	<?endif?>
	
	<?/*
	<h3><?=GetMessage("COLLECTED_REVIEWS_REVIEWS_TITLE")?></h3>	
	*/?>
	
	<?if($arParams['SHOW_RATING_TOTAL'] == 'Y'):?> 
	<br/>
	<div class="collected_reviews_rating common" title="<?=$arResult["RATING"]. " " .GetMessage("COLLECTED_REVIEWS_USERS_OT") . ' ' .$arResult["USER_COUNT"] . ' ' . GetMessage("COLLECTED_REVIEWS_RATING_USER")?>">
		<div style="float: left; font-size: 12px !important; padding-right: 10px;"><?=GetMessage("COLLECTED_REVIEWS_TOTAL_RATING")?>:</div>
		<span class="star<?if($arParams['RATING_SHORT_TEMPLATE'] == 'Y'):?> short<?endif;?>">
			<span style="width:<?=$arResult["WIDTH"]?>%;"></span>
		</span>
		<?if($arResult["USER_COUNT"]):?>
		<div class="users-counter">
			<?=GetMessage("COLLECTED_REVIEWS_USERS_OT")?> <?=$arResult["USER_COUNT"];?> 
			<?=($arResult["USER_COUNT"] == 1 || ($arResult["USER_COUNT"]-1) % 10 == 0) ? GetMessage("COLLECTED_REVIEWS_RATING_USER") : GetMessage("COLLECTED_REVIEWS_RATING_USERS");?>
		</div>
		<?endif;?>
	</div> 
	<?endif;?>

	<?if(count($arResult["ITEMS"])):?>

		<?foreach($arResult["ITEMS"] as $arMessage):?>
		<div class="review" data-id="<?=$arMessage['ID'];?>">
			
			<?if($arParams['SHOW_RATING'] != 'N'):?> 
			<div class="collected_reviews_rating">
				<span class="star star16<?if($arParams['RATING_SHORT_TEMPLATE'] == 'Y'):?> short<?endif;?>" title="<?=GetMessage("COLLECTED_REVIEWS_RATING_DESC")?>: <?=$arMessage["RATING"]?>">
					<span style="width:<?=$arMessage["WIDTH"]?>%;"></span>
				</span>
			</div>
			<?endif;?>
			
			<?if($arMessage['EXP_USING']):?>
			<div class="experience">
				<?=GetMessage("COLLECTED_REVIEWS_EXP_USING")?>: <?=$arMessage['EXP_USING'];?>
			</div>
			<?endif;?>

			<?if($arResult["EDIT_ACCESS"]):?>
				<div class="control">
					<a class="remove" data-action="review.admin.remove" data-id="<?=$arMessage['ID'];?>" href="#" title="<?=GetMessage("COLLECTED_REVIEWS_REVIEW_REMOVE")?>"></a>
					<a class="deactvate"  data-action="review.admin.reject" data-id="<?=$arMessage['ID'];?>" href="#" title="<?=GetMessage("COLLECTED_REVIEWS_REVIEW_DEACTIVATE")?>"></a>
				</div>
			<?endif;?>
				
			<div class="clear"></div>
			
			<?if($arMessage['DIGNITY'] && $arParams['REVIEW_FULL'] != 'N'):?>
			<div class="text">
				<span><?=GetMessage("COLLECTED_REVIEWS_DIGNITY_DESC")?>:</span>
				<?=$arMessage['DIGNITY'];?>
			</div>
			<?endif;?>
			
			<?if($arMessage['LIMITATIONS'] && $arParams['REVIEW_FULL'] != 'N'):?>
			<div class="text">
				<span><?=GetMessage("COLLECTED_REVIEWS_LIMITATIONS_DESC")?>:</span>
				<?=$arMessage['LIMITATIONS']?>
			</div>
			<?endif;?>
			
			<?if($arMessage['COMMENTS']):?>
			<div class="text">
				<?if($arParams['REVIEW_FULL'] != 'N'):?>
				<span><?=GetMessage("COLLECTED_REVIEWS_COMMENTS_DESC")?>:</span>
				<?endif;?>
				<?=$arMessage['COMMENTS'];?>
			</div>
			<?endif;?>

			<div class="review-info">
				<div class="date"><?=$arMessage['DATE_CREATE'];?></div>
				<div class="user"><?=$arMessage['USER_NAME'];?></div>
				<?if($arMessage['CITY']):?>
				<div class="city"><?=$arMessage['CITY'];?></div>
				<?endif;?>
				
				<div class="vote">
					<span><?=GetMessage("COLLECTED_REVIEWS_HELPFUL")?></span>
					<a href="#" class="useful<?if($arMessage['VOTED']) echo " voted";?>"  data-action="review.vote.useful" data-id="<?=$arMessage['ID'];?>">
						<label><?=GetMessage("COLLECTED_REVIEWS_HELPFUL_Y")?></label><span><?=$arMessage['HELPFUL'];?></span>
					</a>
					<span class="sep"> / </span>
					<a href="#" class="useless<?if($arMessage['VOTED']) echo " voted";?>" data-action="review.vote.useless" data-id="<?=$arMessage['ID'];?>">
						<label><?=GetMessage("COLLECTED_REVIEWS_HELPFUL_N")?></label><span><?=$arMessage['USELESS'];?></span>
					</a>
				</div>
				
			</div>

		</div>
		<?endforeach;?>

		<?if(!$arParams["DISPLAY_TOP_PAGER"] && !$arParams["DISPLAY_BOTTOM_PAGER"] && $arResult["COUNT"] > $arParams['REVIEWS_COUNT']):?>
			<div class="clear"></div>
			<a href="#" class="btn-getmore" data-action="review.load.more"><?=GetMessage('COLLECTED_REVIEWS_LOAD_MORE')?></a>
			<a href="#" class="btn-getmore" data-action="review.load.all"><?=GetMessage('COLLECTED_REVIEWS_LOAD_ALL')?> (<?=$arResult["COUNT"];?>)</a>
		<?endif;?>
		
	<?else:?>
		<p><?=GetMessage("COLLECTED_REVIEWS_EMPTY_REVIEW")?></p>
	<?endif;?>
	
</div>
<div style="clear:both;"></div>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>

<script>
var RManager = null;
BX.ready(function(){
	//review list controller
	RManager = new BX.COLLECTEDReviewsManager({
		list_container: "collected_reviews_list_<?=$arResult["LIST_ID"]?>",
		item_class: "review",
		review_template: "collected_review_tmpl",
		
		items_total: <?=count($arResult["ERRORS"])?>,
		element_id: <?=$arParams['ELEMENT_ID']?>,
		loaded_number: 	<?=$arParams['LOADED_NUMBER']?>,

		message_remove_confirm: '<?=GetMessage('COLLECTED_REVIEWS_REVIEW_DEL_CONFIRM')?>',
		message_reject_confirm: '<?=GetMessage('COLLECTED_REVIEWS_REVIEW_DA_CONFIRM')?>',
		message_already_voted:  '<?=GetMessage('COLLECTED_REVIEWS_ALREADY_VOTED')?>',
		
		action_vote: function(id, action, obj, obj_cur, count){
			//event when registered to vote
			for(i in obj_cur){
				if(i != 'indexOf')
					var item = BX.findChild(obj_cur[i], {tag: 'span'}, true, false);
					BX.adjust(item, {html: count});
				}
			}
		});
	}); 
</script>




<? //  шаблон для подгрузки отзывов ?>

<script type="text/html" id="collected_review_tmpl">
	
	<div class="collected_reviews_rating" title="<?=GetMessage("COLLECTED_REVIEWS_RATING_DESC")?>: <%=RATING%>">
		<span class="star star16<?if($arParams['RATING_SHORT_TEMPLATE'] == 'Y'):?> short<?endif;?>" >
			<span style="width:<%=WIDTH%>%;"></span>
		</span>
	</div>
	
	<?if($arMessage['EXP_USING']):?>
	<div class="experience"><?=GetMessage("COLLECTED_REVIEWS_EXP_USING")?>: <%=EXP_USING%></div>
	<?endif;?>

	<%if(RIGHT >= "W"){ %>
	<div class="control">
		<a class="remove" data-action="review.admin.remove" data-id="<%=ID%>" href="#" title="<?=GetMessage("COLLECTED_REVIEWS_REVIEW_REMOVE")?>"></a>
		<a class="deactvate"  data-action="review.admin.reject" data-id="<%=ID%>" href="#" title="<?=GetMessage("COLLECTED_REVIEWS_REVIEW_DEACTIVATE")?>"></a>
	</div>
	<% } %>
 
	<div class="clear"></div>
	
	<% if(DIGNITY != "") { %><div class="text"><span><?=GetMessage("COLLECTED_REVIEWS_DIGNITY_DESC")?>:</span><%=DIGNITY%></div><% } %>
	<% if(LIMITATIONS != "") { %><div class="text"><span><?=GetMessage("COLLECTED_REVIEWS_LIMITATIONS_DESC")?>:</span><%=LIMITATIONS%></div><% } %>
	<% if(COMMENTS != "") { %><div class="text"><span><?=GetMessage("COLLECTED_REVIEWS_COMMENTS_DESC")?>:</span><%=COMMENTS%></div><% } %>
	
	<div class="review-info">
		<div class="date"><%=DATE_CREATE%></div> 
		<div class="user"><%=USER_NAME%></div>
		<div class="city"><%=CITY%></div>

		<div class="vote">
			<span><?=GetMessage("COLLECTED_REVIEWS_HELPFUL")?></span>
			<a href="#" class="useful<%if(VOTED) {%> voted<%}%>"  data-action="review.vote.useful" data-id="<%=ID%>">
				<label><?=GetMessage("COLLECTED_REVIEWS_HELPFUL_Y")?></label><span><%=HELPFUL%></span>
			</a>
			<span class="sep"> / </span>
			<a href="#" class="useless<%if(VOTED) {%> voted<%}%>" data-action="review.vote.useless" data-id="<%=ID%>">
				<label><?=GetMessage("COLLECTED_REVIEWS_HELPFUL_N")?></label><span><%=USELESS%></span>
			</a>
		</div>

	</div> 
</script>

<? //  конец - шаблон для подгрузки отзывов ?>
