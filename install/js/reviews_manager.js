/*
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

;(function(window, document) {

	if (window.BX.COLLECTEDReviewsManager)
		return;
		
	var
		BX = window.BX,
		controller,
		defaultConfig = {
			//debug		: false, 
			list_container	: null,
			item_class		: "review",
			
			items_total		: 100,
			element_id		: null,
			loaded_number	: 5,
		
			review_template: null,
			selector_office_block : "selector-office",
			
			message_remove_confirm: 'remove_confirm',
			message_reject_confirm: 'reject_confirm',
			message_already_voted:  '',
		

			//callback
			action_vote: function(){},
			action_admin: function(){},
			action_load: function(){}
			},
		config = {}, 
		data = {};
		
	
	BX.COLLECTEDReviewsManager = function(params){

		if(typeof params != "object")
			params = {};

		config = params;

		//config
		for (var i in defaultConfig) {
			if (typeof (params[i]) == "undefined") 
				config[i] = defaultConfig[i]; 
			}
			
		config.loaded_number = config.loaded_number - 0;
			
		this.data = data;
		this.attr_action = "data-action";
		this.attr_id = "data-id";
		this.controls = {};
		this.actions = {
			"review":{
				"vote": { 
					"useful": function(object, id){},
					"useless": function(object, id){},
					"common": function(object, id, action, action_full){

						if(BX.hasClass(object, "voted") && config.message_already_voted != "")
							alert(config.message_already_voted);

						controller.SendAction(id, action_full, action, function(result){

							if(result["COUNT"] != 0) {
								
								var obj = controller.getControls(action_full + ".", id);
								var obj_cur = controller.getControls(action_full + "." + action, id);
		
								for(i in obj){
									if(i != 'indexOf')
										BX.addClass(obj[i], "voted");
									}
								
								if(typeof config.action_vote == "function")
									config.action_vote(id, action, obj, obj_cur, result["COUNT"]);
								
								}

							if(result["ERROR"] == true) {

								for(i in result["ERRORS"]){
							
									if(i == "indexOf")
										continue;
										
									alert(result["ERRORS"][i]);
										
									}
								}
								
							});
						}
					},

				"admin": {
					"remove": function(object, id){
						if(!confirm(config.message_remove_confirm))
							return false;
						},
					"reject": function(object, id){
						 
						if(!confirm(config.message_reject_confirm))
							return false;
						
						},
					"common": function(object, id, action, action_full){
						
						controller.SendAction(id, action_full, action, function(result){
							
							if(result["ERROR"] == false) {
								
								//parent of object is item_class - him remove
								var parent = BX.findParent(object, {className: config.item_class}, 1);
								
								BX.remove(parent);
								
								//if(typeof config.action_admin == "function")
								//	config.action_admin(id, action, obj, obj_cur, result["COUNT"]);
								}
							else {
								for(i in result["ERRORS"]) {
							
									if(i == "indexOf")
										continue;
										
									alert(result["ERRORS"][i]);
										
									}
								}
								
							});
						}
					},

				"load": {
					"more": function(){},
					"all": function(){},
					"common": function(object, id, action, action_full){

						if(typeof config.loaded_number != 'number')
							config.loaded_number = 5;
							
						var newloaded = 0;
						
						if(action == "more")
							newloaded = config.loaded_number;

						var items = BX.findChild(BX(config.list_container), {className: config.item_class}, true, true);
						var reviews_loaded = items.length;

						controller.reviewsLoad(reviews_loaded, newloaded, config.items_total, config.element_id);

						if(typeof config.action_load == "function")
							config.action_load(object, id, action);

						}
					}
				}
			};
			// -- actions --;
		
		if(config.list_container == null)
			return false;
		
		//init controls
		this.initControls();
		
		controller = this;
			
		return this;
		};
		
			
	BX.COLLECTEDReviewsManager.prototype.SetAction = function(object, action, id){
	
		var a = action.split('.');
			
		var obj;
		var ar = {};
		var act = "";
		var result = true;
		for (var i=0; i < a.length; i++){

			if(i == 0)
				obj = this.actions[a[i]];
			else
				obj = obj[a[i]];

			ar[i] = obj;

			if(typeof obj == "function"){
				if(a[i] != "common") {
					result = obj(object, id, a[i]);
					}			
				}
			}
		
		
		//*** call common ************************
		if(result !== false) {
		
			for (var i = a.length-1; i >= 0; i--){
				var temp = [];
				
				for(var t = 0; t <= i; t++){
					temp.push(a[t]);
					}

				if(typeof ar[i]["common"] == "function") {
					var act2 = temp.join('.');
					ar[i]["common"](object, id, a[i+1], act2);
					}
				}
		
			}
			
			
		}



	BX.COLLECTEDReviewsManager.prototype.initControls = function() {
		
		//init event for controls
		this.controls = BX.findChild(BX(config.list_container), {"attr": this.attr_action}, true, true);
		
		//create events
		for(i in this.controls) {			
			
			if(i == "indexOf")
				continue;

			var test = false, 
				attr = "",
				pos = -1;

			if(typeof this.controls[i] == "undefined") 
				continue;

			attr = this.controls[i].getAttribute(this.attr_action);

			//check attr
			for(t in this.actions){

				if(t == "indexOf")
					continue;
				
				var re = new RegExp("^"+t+".", "g"),
					pos = attr.search(re);

				if(pos === 0)
					break;
				}

			if(pos !== 0)
				continue;

			BX.unbind(this.controls[i], 'click', BX.proxy(this.Click, this.controls[i]));	
			BX.bind(this.controls[i], 'click', BX.proxy(this.Click, this.controls[i]));
		
			}
			// -- create events
		};

	BX.COLLECTEDReviewsManager.prototype.Click = function(e){
	
		var id   = this.getAttribute("data-id") - 0,
			attr = this.getAttribute("data-action");
	
		controller.SetAction(this, attr, id);
		BX.PreventDefault(e);
		return false;			
		}
					
	BX.COLLECTEDReviewsManager.prototype.getControls = function(action, id, callback) {

		var obj = [];

		if(typeof id != "number")
			id = 0;

		id = id < 0 ? 0 : id ;

		for(i in this.controls) {

			if(i == "indexOf")
				continue;

			var attr_act = "",
				attr_id = 0,
				re = new RegExp("^"+action, "i");

			if(typeof this.controls[i] == "undefined") 
				continue;

			attr_act = this.controls[i].getAttribute(this.attr_action);
			attr_id = this.controls[i].getAttribute(this.attr_id)-0;

			if(attr_act.search(re) === 0 && attr_act != null && 
				((id > 0 && id == attr_id) || id == 0 )
			){
				obj.push(this.controls[i]);
				if(typeof callback == "function")
					callback(this.controls[i]);
				}
			}
				
			return obj;
		}
		//-- getControls
	
	
				
	BX.COLLECTEDReviewsManager.prototype.getTmpl = function(str) {
		var fn = new Function("obj",
			"var p=[],print=function(){p.push.apply(p,arguments);};" +
			"with(obj){p.push('" + document.getElementById(str).innerHTML
			  .replace(/[\r\t\n]/g, " ")
			  .split("<%").join("\t")
			  .replace(/((^|%>)[^\t]*)'/g, "$1\r")
			  .replace(/\t=(.*?)%>/g, "',$1,'")
			  .split("\t").join("');")
			  .split("%>").join("p.push('")
			  .split("\r").join("\\'") + "');} return p.join('');");
		return fn
		};
		
	
	
	BX.COLLECTEDReviewsManager.prototype.reviewsLoad = function(count_loaded, count, count_total, element_id) {
	
		BX.ajax({
			url: "/bitrix/tools/collected.reviews/load_rewiews.php",
			data: {
				'COUNT': count_loaded,
				'COUNT_LOAD': count,
				'COUNT_TOTAL': count_total, 
				'ELEMENT_ID': element_id
			},
			method: 'POST',
			dataType: 'JSON',
			async: true,
			cache: false,
			onsuccess: BX.proxy(this._reviewsLoad, this),
			//onfailure: BX.proxy(this._Error, this)
			});
		}	
		
	BX.COLLECTEDReviewsManager.prototype._reviewsLoad = function(res){
		
		if(res['LOADED_ALL'] == true){
			
			//hide conrols

			var more = this.getControls('review.load.more');
			var all = this.getControls('review.load.all');
			
			for(i in all){
				if(i != "indexOf")
					BX.hide(all[i]);
				}
				
			for(i in more){
				if(i != "indexOf")
					BX.hide(more[i]);
				}

			}
			
		
		if(res['ERROR'] === true || typeof res['DATA'] != 'object'){
			//error event
			//config.error(res);
			}
		else {

			for(i in res['DATA']){
			
				if(i == "indexOf")
					continue;
			
				var tmp = '';
				if(typeof res['DATA'][i] == 'object'){
					for(p in res['DATA'][i]){
						tmp = this.getTmpl(config.review_template)(res['DATA'][i]); 
						}
					}
				
				//*****************************
				//insert after last item
				//*****************************
				var items = BX.findChild(BX(config.list_container), {className: config.item_class}, true, true);

				if(items == null) {
					alert('items not found');
					}
				else{
					var last_item = items[items.length - 1],
						elem = BX.create('DIV', {'props':{className:config.item_class}, style: {}}); 
						elem.innerHTML = tmp;
						
						if(typeof last_item != "undefined")
							BX(config.list_container).insertBefore(elem, last_item.nextSibling);
						else
							BX(config.list_container).appendChild(elem);
							
						
					}
				}
			
			this.initControls();

			//config.success(res);
	
			}
		}; 
		
	BX.COLLECTEDReviewsManager.prototype.SendAction = function(element_id, action_path, action, success) {
	
		BX.ajax({
			url: "/bitrix/tools/collected.reviews/actions.php",
			data: {
				'ID': element_id,
				'ACTION': action_path + "." + action
				},
			method: 'POST',
			dataType: 'JSON',
			async: true,
			cache: false,
			onsuccess: function(result){
				if(typeof success == "function")
					success(result);
				},
			onfailure: BX.proxy(this._Error, this)
			});
			
		
		};

	BX.COLLECTEDReviewsManager.prototype._Error = function(err , d) {
		alert(err + " " + d); 
		};
	
	
							
	
		
	})(window, document);
 