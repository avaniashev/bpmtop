function manageAdminFilter(){
	var $showFilter = $('#show-filter'),
		$cleanFilter = $('.JS-clean-filter'),
		$extendFilter = $('#extend-filter'),
		$filter = $('.filter');


    $showFilter.click(function(){
    	var _a = $(this);
    	if(_a.hasClass('active')){
    		_a.removeClass('active');
            $extendFilter.hide();
		}else{
            _a.addClass('active');
            $extendFilter.show();

            $extendFilter.find('.filterGroup').each(function(index, group){
                var height = $(group).height();
                if (height < 50){
                    $('.oneLine').append($(group));
                } else if (height < 150){
                    $('.twoLine').append($(group));
                } else {
                    $('.multiLine').append($(group));
                }
            });
		}
		return !1;
	});
	$cleanFilter.on('click', function(event){
		event.preventDefault();
		$filter.find('input, textarea').each(function(i, _input){
			var inputClear = $(_input),
				defaultValue = inputClear.data('default'),
				typeInput = inputClear.attr('type');

			switch(typeInput){
				case 'checkbox':
					inputClear.prop('checked', false);
					break;
				case 'radio':
					inputClear.prop('checked', false);
					break;
				case 'hidden':
					break;
				case 'submit':
					break;
				default:
					if (defaultValue) {
						inputClear.val(defaultValue);
					} else {
						inputClear.val('');
					}
					break;
			}
		});
		$filter.find('select').val([]).trigger('chosen:updated'); // chosen
        $filter.find('select').val([]).trigger("change"); // select2

        var clearFilterBtn = $('#ClearFilterBtn');
        if (clearFilterBtn.length > 0) {
            clearFilterBtn.val(1);
        }

		$('.search-choice-close').trigger('click');

		$('.JS-clean-filter').trigger('completeClearForm');
	});

}

$(function () {
	manageAdminFilter();
    if ($.datepicker){
		$('.filter-block .datepicker').datepicker({
			dateFormat: "dd.mm.yy"
		});
	}
});
