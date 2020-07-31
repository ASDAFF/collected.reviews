/*
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

;(function(window) {

	if (window.BX.COLLECTEDReviewsRating)
		return;

	var
		BX = window.BX,
		defaultConfig = {
			container: 'collected_reviews_form',
			selector:	{name: 'RATING'},
			starConteiner: 'collected_rating',
			onChange: function(){}
			},
		config = {},
		container = null,
		input = null;

	BX.COLLECTEDReviewsRating = function(params){

		config = params;
		
		for (var i in defaultConfig)
			if (typeof (params[i]) == "undefined") 
				config[i] = defaultConfig[i];
		
		container = BX(config.container);
		var rating_selector = BX.findChild(container, { tagName: 'select', attr: config.selector}, true, true);
		
		for (i in rating_selector) {
			
			if(!rating_selector[i].getAttribute('name'))
				rating_selector[i].setAttribute('name', 'selector_' + i);

			;(function() {
				//create hidden input
				var input = BX.create('INPUT', {
					props: {
						type: 		'hidden',
						name: rating_selector[i].getAttribute('name')
						}
					});
				
				var option = BX.findChild(rating_selector[i], { tagName: 'option'}, true, true);
		
				//create star container
				var star_container = BX.create('DIV', {props: {className: config.starConteiner}});
				
				for (k in option) {

					var value = option[k].value;
					var text = option[k].text;
						
					var item = BX.create('a', {
						props: {
							'href': '#'+value,
							'title': text
							},
						attrs: {
							'data-rating': value
							}, 
						events: {
							'mouseover': function(){
								
								var r = BX.findChildren(BX(this.parentNode), {tagName: 'a'}, true);
								var flag = false;
								
								for (i in r){
									if(flag == false)
										BX.addClass(r[i], 'hover');
									
									if(r[i] == this && flag == false)
										flag = true;
									}
								},
							'mouseout': function(e){
								var r = BX.findChildren(BX(this.parentNode), {tagName: 'a'}, true);
								for (i in r){
									if(typeof r[i] == 'object'){
									var item_value = r[i].attributes['data-rating'].value.replace('#','') - 0;
										if(item_value > input.value)
											BX.removeClass(r[i], 'hover');
										}
									}
								},
							'click': function(e){
								value = this.attributes['data-rating'].value;
								value = value.replace('#','');
								input.value = value;
								
								var r = BX.findChildren(BX(this.parentNode), {tagName: 'a'}, true);
								
								for (i in r){
									if(i > value - 1)
										BX.removeClass(r[i], 'hover');
									}

								config.onChange(value);
								BX.PreventDefault(e);
								}
							}
						});

					//проверяем выбранный пункт
					if(rating_selector[i].value > value - 1 ){
						BX.adjust(item, {props: {className: 'hover'}});
						input.value = value;
						}
					//insert in container
					star_container.appendChild(item);
					}
				

				//insert hidden input
				rating_selector[i].parentNode.insertBefore(input, rating_selector[i].nextSibling);
				rating_selector[i].parentNode.insertBefore(star_container, rating_selector[i].nextSibling);
			})();
			//remove selector
			BX.remove(rating_selector[i]);
			}
		
		
		//this.SetRating = function(value){
		//	this._SetRating(value);
		//	};

		return this;
		};
	
	BX.COLLECTEDReviewsRating.prototype._SetRating = function(value) {
		//console.log(value);
		//config.onChange(value);
		}

	})(window);

	//;(function(window) {})(window);
	