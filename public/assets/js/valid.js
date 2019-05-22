(function($){
	$.validInput = function(input,regexp){
		var result =new Array(),
			i = 0;
		$.each(input,function(index,content){
			var value = $(content).val(),
				key = $(content).attr("id");
			if(!regexp.test(value)){
				result[i] = key;
				i++;
			}
		})
		return result;
	}
})(jQuery);