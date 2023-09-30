$(function () {
	$('.small-filter-info .clear-item').click(function (event) {
		event.preventDefault();
		let _a = $(this),
			alias = _a.data('alias'),
			modelName = _a.data('model'),
			_filterBlock = $('#extend-filter'),
			findSelector = $("[data-alias='" + alias + "']", _filterBlock);

		if (findSelector.length < 1) {
			findSelector = $("[data-field='" + modelName + "." + alias + "']", _filterBlock);
		}

		if (findSelector.length > 0) {
			let type = findSelector[0].nodeName.toLowerCase(),
				values = $(findSelector[0]).val();

			if (type === 'input') {
				$(findSelector[0]).val('');
				$(findSelector[0]).prop('checked', false);
				_a.parent().first().hide();
			} else if (type === 'select') {
				console.log(values);
				if (values === null || values === undefined) {

				} else {
					if (values.length > 0) {
						$.each(values, function (i, val) {
							$(findSelector[0]).find($("option[value='" + val + "']")).removeAttr('selected');
						});
					}
				}

				triggerUpdate($(findSelector[0]));
				_a.parent().first().hide();
			}
		} else {
			_a.parent().first().hide();
		}
	});

	$('.JS-clean-filter').on('completeClearForm', function () {
	    let SmallFilterBlock = $('.small-filter-block');
        SmallFilterBlock.html('').hide();

		let extendFilter = $('.extend-filter');
		if (extendFilter.length > 0) {
			let btnSubmit = extendFilter.find('[type="submit"]');
			if (btnSubmit.length > 0) {
                btnSubmit = btnSubmit.eq(0);
				let FilterForm = btnSubmit.closest('form'),
                    FilterFormActon = FilterForm.attr('action');
				if(FilterFormActon !== undefined && FilterFormActon !== ''){
                    window.location.href = FilterFormActon+'?clear_filter=1';
                    return !1;
                }else{
                    btnSubmit.click();
                }
			}
		}
	});

	function triggerUpdate(input) {
		input.trigger('chosen:updated');
		input.trigger('liszt:updated');
		input.trigger('change.select2');
		input.trigger('change');
	}
});
