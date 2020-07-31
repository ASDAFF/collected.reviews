/*
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

BX.ready(function(){
	var form = BX.findChild(BX('collected_reviews_subscribe_form'), {tagName: 'form'}, true, false);
	var errors = form.attributes['data-errors'].value;
	
	if(errors == 0)
		BX.hide(form);
		
	var open = BX.findChildren(BX('collected_reviews_subscribe_form'), {className: 'open'}, true);
	
	for (i=0; i < open.length; i++)	{
	
		BX(open[i]).onclick = function(){
			
			if(BX.isNodeHidden(form))
				BX.show(form);
			else
				BX.hide(form);
			
			return false;
			};
		}

	});
