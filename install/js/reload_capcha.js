;(function(window) {

	if (window.BX.CapchaReloader)
		return;

	var
		BX = window.BX,
		defaultConfig = {
			captcha_sid:  	'',
			captcha_word: 	'',
			captcha_img:  	'',
			success: 		function(){}
			},
		config = {},
		captcha_img = null,
		captcha_word = null,
		captcha_sid = null;
	
	BX.CapchaReloader = function(params){
	
		config = params;
		
		for (var i in defaultConfig)
			if (typeof (params[i]) == "undefined") 
				config[i] = defaultConfig[i];
		
		captcha_img = BX(config.captcha_img);
		captcha_word = BX(config.captcha_word);
		captcha_sid = BX(config.captcha_sid);
		_this = this;
		this.Reload = function(){
			BX.ajax.getCaptcha(BX.proxy(this.Update, this));
			}
		return this;
		};

	BX.CapchaReloader.prototype.Update = function(data){
		console.log('UpdateElements');
		captcha_word.value = '';
		captcha_sid.value = data['captcha_sid']; 
		
		BX.adjust(captcha_img, { 
			attrs:{'src': '/bitrix/tools/captcha.php?captcha_sid=' + data['captcha_sid']} 
			});
		config.success();
		};

	})(window);
	
	