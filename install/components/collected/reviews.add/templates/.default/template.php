<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="collected_reviews_form" id="collected_reviews_form">
	<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(array_key_exists('ajax', $_REQUEST) && $_REQUEST['ajax']=='Y')
		$APPLICATION->RestartBuffer();
	?>

	<?if (strlen($arResult["MESSAGE"]) > 0):?>
		
		<a class="open opened"><?=GetMessage("REVIEWS_ADD_TITLE")?></a>
		<div class="sucess"><?=$arResult["MESSAGE"]?></div>
		
		<script type="text/javascript">
			top.BX.scrollToNode(top.BX('collected_reviews_form'));
		</script>
		
	<?else:?>
		
		<a class="open opened"><?=GetMessage("REVIEWS_ADD_TITLE")?></a>

		<form name="review_add" method="POST" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data" id="REVIEW_ADD_FORM">

			<?=bitrix_sessid_post();?>
			
			<?if (count($arResult["ERRORS"]) || count($arResult["ERRORS_FIELDS"])):?>
				<div class="errors">
					<p><?=GetMessage("THERE_ERRORS")?></p>
				<?=implode("<br />", $arResult["ERRORS"]);?>
				</div>
				<script type="text/javascript">
					top.BX.scrollToNode(top.BX('REVIEW_ADD_FORM'));
				</script>
			<?endif?>

			<input type="hidden" name="captcha_sid" id="review_captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>"/>

			<?foreach($arResult['FIELDS'] as $ID => $arField):?>
			<?
			$has_error = array_key_exists($ID, $arResult['ERRORS_FIELDS']);
			?>
			<div class="inp-area<?=$has_error ? " error" : ""?>">
				
				<label class="title">
					<span><?=$arField["NAME"];?> <?if($arField['REQUIRED'] == 'Y'):?><i class="required">*</i><?endif?></span>
					<span>
				
				<?if($arField['TYPE'] == 'S' || $arField['TYPE']==''):?>
					<input type="text" name="<?=$ID?>" id="field<?=$ID?>" value="<?=$arField["VALUE"];?>" <?=$arField["ATTR"];?>/>
				<?elseif($arField['TYPE'] == 'T'):?>
					<textarea name="<?=$ID?>" id="field<?=$ID?>"><?=$arField["VALUE"];?></textarea>
				<?elseif($arField['TYPE'] == 'L'):?>
				
					<?switch($arField['LIST_TYPE'])
					{
						case 'C':
						?>
						<?if($arField['REQUIRED'] == 'Y'):?>
							<?foreach($arField["VALUES"] as $value_id => $value):?>
							<label><input type="radio" name="<?=$ID?>" value="<?=$value_id?>" <?if($value_id == $arField["VALUE"]):?>checked<?endif;?>/><?=$value?></label>
							<?endforeach;?>
						<?elseif($arField['REQUIRED'] != 'Y'):?>
							<?foreach($arField["VALUES"] as $value_id => $value):?>
							<label><input type="checkbox" name="<?=$ID?>" value="<?=$value_id?>" <?if($value_id == $arField["VALUE"]):?>checked<?endif;?>/><?=$value?></label>
							<?endforeach;?>
						<?endif;?>
						<?
						break;
						
						default:
						?>
						<select name="<?=$ID?>" id="field<?=$ID?>">
							<?foreach($arField["VALUES"] as $value_id => $value):?>
							<option value="<?=$value_id?>" <?if($value_id == $arField["VALUE"]):?>selected<?endif;?>><?=$value?></option>
							<?endforeach;?>
						</select>
						<?
						break;
					}
					?>
				<?endif;?>
					<?if($has_error):?>
					<em class="error<?if($arField['TYPE'] == 'T'):?> ta<?endif;?>"><i><?=$arResult['ERRORS_FIELDS'][$ID];?></i></em>
					<?endif;?>
					</span>
				</label>
			</div>
			<?endforeach;?>
			
			
			<?if($arParams['SUBSCRIBE'] == 'Y1'):?>
			<div class="inp-area-option">
				<label class="option"><input type="checkbox" name="<?=$ID?>" value="<?=$arField["VALUE"];?>"/><?=GetMessage("REVIEWS_SUBSCRIBE_DESC")?></label>
			</div>
			<?endif;?>
				
			<?if($arParams["USE_CAPTCHA"]=='Y'):?>
			<?
			$has_error = array_key_exists("captcha_word", $arResult['ERRORS_FIELDS']);
			?>
			<div class="inp-area captcha<?=$has_error ? " error" : "" ;?>">	
				<div class="inp">
					<label class="title"><?=GetMessage("CAPTCHA_CODE_DESC")?><i class="required">*</i></label>
					<input type="text" name="captcha_word" id="review_captcha_word" maxlength="50" value="" placeholder="<?=GetMessage("CAPTCHA_CODE_PH")?>"/>	<br/>
					<?if($has_error):?>
					<em class="error"><i><?=$arResult['ERRORS_FIELDS']["captcha_word"];?></i></em>
					<?endif;?>
				</div>		
				<div class="img">
					<img id="review_captcha_img" src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt=""/>
					<a href="#" class="reload"  id="collected_reviews_capcha"><?=GetMessage("CAPTCHA_CODE_RELOAD_DESC")?></a>
				</div>	
			</div>
			<?endif;?>

			
			
			<div class="inp-area buttons">
				<input type="submit" class="update" name="review_submit" value="<?=GetMessage("SEND")?>"/>
				<div class="loader"></div>
				<span class="req"><i class="required">*</i> &mdash; <?=GetMessage("REQUIRED_FIELDS");?></span>
			</div>
		</form>
		
	<script>
		var in_proccessed = false,
			errors_f = <?=CUtil::PhpToJSObject($arResult["ERRORS_FIELDS"]);?>;
			errors = <?=CUtil::PhpToJSObject($arResult["ERRORS"]);?>;

			BX.ready(function(){

				var container = BX.findChild(document, {className: 'collected_reviews_form'}, true, false),
					open_link = BX.findChild(container, {className: 'open'}, true, true),
					form = BX.findChild(container, {tagName: 'form'}, true, false),
					path = "";
					
				path = form.getAttribute("action");

				if (errors==0 && errors_f == 0)
					BX.hide(form);
					
				for (i in open_link)	{
					
					if (errors==0)
						BX.removeClass(open_link[i], 'opened');
					
					BX(open_link[i]).onclick = function(){
						if (BX.style(form, 'display') == 'none'){ //if(BX.isNodeHidden(form)){
							BX.show(form);
							BX.addClass(this, 'opened');
							}
						else {
							BX.hide(form);
							BX.removeClass(this, 'opened');
							}
						return false;
						};
					}

				BX(form).onsubmit = function(){
				
					var submit = BX.findChild(BX(form), {tagName: 'input', attr:{'type':'submit'}}, true, true);
					var loader = BX.findChild(BX(form), {className: 'loader'}, true, false);
					if(loader)
						BX.show(loader);
					
					for (i in submit){
						submit[i].value = '<?=GetMessage("SENDING")?>';
						}
				
					var form_data = {};
					form_data['ajax'] = 'Y';
					
					if(in_proccessed == true)
						return false;
					
					in_proccessed = true;
					
					for (i=0; i < BX(this).elements.length; i++){
						el = BX(this).elements[i];
						
						if (el.name )	{ //&& el.name != 'sessid'
							var n = el.name, v = '', t = el.type.toLowerCase();
							switch (t){
								case 'button':
								case 'submit':
								case 'reset':
								case 'image':
								case 'file':
								case 'password':
									break;

								case 'radio':
								case 'checkbox':
									if (el.checked)
										v = el.value || 'on';
								break;

								case 'select-multiple':
									n = n.substring(0, n.length-2);
									v = [];
									for (j=0;j<el.options.length;j++)
									{
										if (el.options[j].selected)
										{
											v.push(el.options[j].value);
										}
									}
								break;

								default:
									v = el.value;
								}
							
							if (n.indexOf('[]') > 0){
								if (typeof(form_data[n]) == 'undefined')
									form_data[n] = [v];
								else
									form_data[n].push(v);
								}
							else
								form_data[n] = v;
							
							}
						}
					
					form_data['review_submit'] = 'Y';
					
					BX.ajax({
						url: path,
						method: 'POST',
						dataType: 'HTML',
						async: true,
						data: form_data,
						cache: false,
						onsuccess: function(data){
							container.innerHTML = data;
							in_proccessed = false;
							if(loader)
								BX.hide(loader);
							for (i in submit){
								submit[i].value = '<?=GetMessage("SEND")?>';
								}
							},
						onfailure: function(){
							in_proccessed = false;
							if(loader)
								BX.hide(loader);
							for (i in submit){
								submit[i].value = '<?=GetMessage("SEND")?>';
								}
							}
						});

					return false;
					}
				
				//Create stars
				BX.COLLECTEDReviewsRating({
					container: 'collected_reviews_form',
					selector:	{name: 'RATING'},
					starConteiner: 'collected_reviews_rating_selector',
					starWidth: 22,
					onChange: function(value){
						//callback onChange selected rating
						}
					});
					
				//capcha reload
				if(BX.CapchaReloader) {

					var RCapcha = new BX.CapchaReloader({
						captcha_sid:  	'review_captcha_sid',
						captcha_word: 	'review_captcha_word',
						captcha_img:  	'review_captcha_img',
						success: 		function(){},
						});

					//link click
					BX.bind(BX('collected_reviews_capcha'), 'click', function(e){
						RCapcha.Reload();
						BX.PreventDefault(e);
						});
					}

				});	
		</script>
	<?endif?>

	<?
	if(array_key_exists('ajax', $_REQUEST) && $_REQUEST['ajax']=='Y')
		die();
	?>
</div>
<div style="clear:both;"></div>