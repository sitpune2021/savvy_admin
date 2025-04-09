import $ from 'jquery';
window.$ = window.jQuery = $;
import select2 from 'select2';
select2();// does nothing

(function ($) {
	"use strict";
	if ($('.js-example-basic-single').length > 0) {
		$('.js-example-basic-single').select2();
	}

})(jQuery);


function handleFormSubmit(formId, actionUrl, method = 'POST', successCallback = null, errorCallback = null) {
	$(formId).on('submit', function (e) {
		e.preventDefault();

		// Remove previous error messages
		$(this).find('.is-invalid').removeClass('is-invalid');
		$(this).find('.invalid-feedback').remove();
		$(this).find('.is-valid').removeClass('is-valid');
		$(this).find('.valid-feedback').remove();

		let formData = new FormData(this);
		let currentMethod = method;
		let Id = $('#id').val();

		if (Id != null && Id != '') {
			if (!actionUrl.includes(`/${Id}`)) {
				actionUrl = actionUrl + '/' + Id;
			}
			currentMethod = 'POST';
			formData.append('_method', 'PUT'); // Simulate PUT request for Laravel
		}

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$.ajax({
			url: actionUrl,
			method: currentMethod,
			data: formData,
			contentType: false,
			processData: false,
			success: function (response) {
				if (successCallback) {
					successCallback(response);
				} else {
					window.location.href = window.Laravel.routeIndex;
				}
			},
			error: function (xhr) {
				if (xhr.status === 422) {
					var errors = xhr.responseJSON.errors;
					$.each(errors, function (key, value) {
						var input = $('[name="' + key + '"]');
						var select2Container = input.next('.select2-container'); // Target the select2 container
						input.addClass('is-invalid');
						if (select2Container.length > 0) {
							select2Container.addClass('is-invalid');
							select2Container.css('--vz-input-border-custom', 'red');
							select2Container.after('<div class="invalid-feedback">' + value[0] + '</div>');
						} else {
							input.after('<div class="invalid-feedback">' + value[0] + '</div>');
						}
					});


				} else {
					console.log(xhr);
					if (errorCallback) {
						errorCallback(xhr);
					} else {
						alert('An error occurred. Please try again.');
					}
				}
			}
		});
	});
}

handleFormSubmit(
	'#orderForm', // form ID
	'/order', // URL to send data to
	'POST', // default method
	function (response) { // success callback
		console.log('Order saved successfully:', response);
		window.location.href = window.Laravel.routeIndex;
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#customerForm', // form ID
	'/customer', // URL to send data to
	'POST', // default method
	function (response) { // success callback
		console.log('customer saved successfully:', response);
		if (response.order) {
			window.location.href = '/order/' + response.order.id + '/assign-driver';
		} else {
			window.location.href = window.Laravel.routeIndex;
		}

	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#driverForm', // form ID
	'/driver', // URL to send data to
	'POST', // default method
	function (response) { // success callback
		console.log('driver saved successfully:', response);
		window.location.href = window.Laravel.routeIndex;
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#plantForm', // form ID
	'/plant', // URL to send data to
	'POST', // default method
	function (response) { // success callback
		console.log('plant saved successfully:', response);
		window.location.href = window.Laravel.routeIndex;
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#productForm', // form ID
	'/product', // URL to send data to
	'POST', // default method
	function (response) { // success callback
		console.log('product saved successfully:', response);
		window.location.href = window.Laravel.routeIndex;
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);


$('input, select, textarea').on('focus', function () {
	$(this).removeClass('is-invalid');
	$(this).next('.invalid-feedback').remove();


});

$('.js-example-basic-single').on('select2:open', function () {
	var select2Container = $(this).next('.select2-container');
	select2Container.css('--vz-input-border-custom', '');
	select2Container.next('.invalid-feedback').remove();
});