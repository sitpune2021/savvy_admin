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


function handleFormSubmit(formId, actionUrl, method = 'POST', subPath = {}, successCallback = null, errorCallback = null) {
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
			if (subPath?.subPath && !actionUrl.includes(subPath.subPath)) {
				if (subPath.method?.toUpperCase() === 'PUT') {
					actionUrl += `/${subPath.subPath}`;
				}
			}
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
					$('.invalid-feedback').remove(); // Remove previous error messages
					$('.is-invalid').removeClass('is-invalid'); // Remove previous error highlighting

					$.each(errors, function (key, value) {
						var escapedKey = key.replace(/\.([^.\[\]]+)/g, '[$1]');
						var input = $('[name="' + escapedKey + '"]');
						var select2Container = input.next('.select2-container');

						input.addClass('is-invalid'); // Add error class for the input field
						if (select2Container.length > 0) {
							select2Container.addClass('is-invalid');
							select2Container.css('--vz-input-border-custom', 'red');

							if (select2Container.next('.invalid-feedback').length === 0) {
								select2Container.after('<div class="invalid-feedback">' + value[0] + '</div>');
							}
						} else {
							if (input.next('.invalid-feedback').length === 0) {
								input.after('<div class="invalid-feedback">' + value[0] + '</div>');
							}
						}
					});
				} else {
					console.log(xhr);
					if (typeof errorCallback === 'function') {
						showErrorAlert(xhr.responseJSON.error);
					} else {
						alert('An error occurred. Please try again.');
					}
				}
			}
		});
	});
}

function showErrorAlert(message) {
	const alertHTML = `
        <div class="alert alert-danger alert-dismissible"
             role="alert"
             style="
                position: fixed;
                top: 80px;
                right: 25px;
				z-index: 1055;
                max-width: 400px;
                transform: translateX(100%);
                transition: transform 0.5s ease-out, opacity 0.5s ease-out;
             ">
            <i class="ri-error-warning-line me-2" style="font-size: 18px; vertical-align: middle; color: #dc3545;"></i>
            <strong>Error:</strong> ${message}
            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                    ></button>
        </div>
    `;

	// Insert into DOM
	document.body.insertAdjacentHTML('beforeend', alertHTML);

	// Get the latest alert
	const alert = document.querySelectorAll('.alert-dismissible.alert-danger');
	const thisAlert = alert[alert.length - 1];

	// Trigger slide-in animation
	requestAnimationFrame(() => {
		thisAlert.style.transform = 'translateX(0)';
		thisAlert.style.opacity = '1';
	});

	// Auto-dismiss after 5 seconds
	setTimeout(() => {
		thisAlert.style.transform = 'translateX(100%)';
		thisAlert.style.opacity = '0';
		setTimeout(() => thisAlert.remove(), 500); // wait for animation
	}, 5000);
}




handleFormSubmit(
	'#orderForm',
	'/order', // URL to send data to
	'POST', // default method
	{},
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
	{},
	function (response) { // success callback
		window.location.href = window.Laravel.routeIndex;
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#driverForm', // form ID
	'/driver', // URL to send data to
	'POST', // default method
	{},
	function (response) { // success callback
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
	{},
	function (response) { // success callback
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
	{},
	function (response) { // success callback
		window.location.href = window.Laravel.routeIndex;
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#routeForm', // form ID
	'/route', // URL to send data to
	'POST', // default method
	{},
	function (response) { // success callback
		window.location.href = window.Laravel.routeIndex;
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#assignRoutesForm', // form ID
	'/assign-route', // URL to send data to
	'POST', // default method
	{},
	function (response) { // success callback
		window.location.href = window.Laravel.routeIndex;
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#shippingForm', // form ID
	'/customer', // URL to send data to
	'POST', // default method
	{
		subPath: 'shipping-address',
		method: 'Put',
	},
	function (response) { // success callback
		// if (response.customer_id) {
		// 	window.location.href = '/customer/' + response.customer_id + '/assign-route';
		// }
		// else {
			window.location.href = window.Laravel.routeIndex;
		// }
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

$(document).ready(function () {
	let addressIndex = 1;

	// ========== Utility Functions ==========


	function manageButtonBlock() {
		$('.address-buttons').remove();
		const total = $('.address-block').length;
		if (total > 0) {
			$('.address-block').last().append(getButtonBlock());
		}
	}

	function getButtonBlock() {
		return `
			<div class="text-end m-3 mt-0 address-buttons">
				<button type="button" class="btn btn-primary cancel-all me-2">Cancel</button>
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		`;
	}

	function generateAddressBlock(index, data = {}, isEdit = false) {
		const deployedSelect = $(`
			<select class="select js-example-basic-single form-control" name="shipping[${index}][machine_deployed]">
				<option value="Yes" ${data.machine_deployed === 'Yes' ? 'selected' : ''}>Yes</option>
				<option value="No" ${data.machine_deployed === 'No' ? 'selected' : ''}>No</option>
			</select>
		`);

		const plantSelect = $(`
			<select class="select js-example-basic-single form-control" name="shipping[${index}][plant_id]" id="plant_id_${index}" ${window.show ? 'disabled' : ''}>
				<option value="">Select Plant</option>
				${window.plants.map(plant => `
					<option value="${plant.id}" ${data.plant_id === plant.id ? 'selected' : ''}>${plant.name}</option>
				`).join('')}
			</select>
		`);

		const routeSelect = $(`
			<select name="shipping[${index}][route_id]" class="select js-example-basic-single form-control" id="route_id_${index}" ${window.show ? 'disabled' : ''}>
				<option value="">Select Route</option>
			</select>
		`);

		const driverSelect = $(`
			<select name="shipping[${index}][driver_id]" class="select js-example-basic-single form-control" id="driver_id_${index}" ${window.show ? 'disabled' : ''}>
				<option value="">Select Driver</option>
			</select>
		`);

		const block = $(`
			<div class="form-group-item card address-block">
				<div class="card-header d-flex justify-content-between align-items-center add-remove">
					<h5 class="form-title">${isEdit ? (data.id ? 'Edit' : 'Create') : ''} Shipping Address</h5>
					<button type="button" class="btn btn-sm btn-danger ${isEdit ? 'remove-address-edit' : 'remove-address'}">Remove</button>
				</div>
				<div class="row align-item-center card-body">
					<input type="hidden" name="shipping[${index}][id]" value="${data.id || ''}">
					<div class="col-sm-12">
						<div class="input-block mb-3">
							<label>Address</label>
							<input name="shipping[${index}][shipping_address]" type="text" class="form-control"
								placeholder="Enter Shipping Address" value="${data.shipping_address || ''}">
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>Country</label>
							<input name="shipping[${index}][shipping_country]" type="text" class="form-control"
								placeholder="Enter Shipping Country" value="${data.shipping_country || ''}">
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>State</label>
							<input name="shipping[${index}][shipping_state]" type="text" class="form-control"
								placeholder="Enter Shipping State" value="${data.shipping_state || ''}">
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>City</label>
							<input name="shipping[${index}][shipping_city]" type="text" class="form-control"
								placeholder="Enter Shipping City" value="${data.shipping_city || ''}">
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>Pin Code</label>
							<input name="shipping[${index}][shipping_pincode]" type="number" class="form-control"
								placeholder="Enter Shipping Pin Code" value="${data.shipping_pincode || ''}">
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-12" id="plant_select">
						<div class="input-block mb-3 plant-container">
							<label>Plant</label>
						</div>
					</div>
					<div class="col-md-4 col-sm-12" id="route_select">
						<div class="input-block mb-3 route-container">
							<label>Routes</label>
						</div>
					</div>
					<div class="col-md-4 col-sm-12" id="driver_select">
						<div class="input-block mb-3 driver-container">
							<label>Drivers</label>
						</div>
					</div>
					<div class="col-md-4 col-sm-12">
						<div class="input-block mb-3">
							<label>Name</label>
							<input name="shipping[${index}][contact_person]" type="text" class="form-control"
								placeholder="Enter Name" value="${data.contact_person || ''}">
						</div>
					</div>
					<div class="col-md-4 col-sm-12">
						<div class="input-block mb-3">
							<label>Mobile No</label>
							<input name="shipping[${index}][contact_person_phone]" type="text" class="form-control"
								placeholder="Enter Mobile No" value="${data.contact_person_phone || ''}">
						</div>
					</div>
					<div class="col-md-4 col-sm-12" id="machine_select">
						<div class="input-block mb-3 deployed-container">
							<label>Deployed</label>
						</div>
					</div>
				</div>
			</div>
		`);

		// Append selects to their containers
		block.find('.deployed-container').append(deployedSelect);
		block.find('.plant-container').append(plantSelect);
		block.find('.route-container').append(routeSelect);
		block.find('.driver-container').append(driverSelect);

		// Initialize Select2
		block.find('select.select').select2();

		return block;
	}

	$('#add-address').on('click', function () {
		const block = generateAddressBlock(addressIndex++, true);
		$('#shipping_address_div').append(block);
	});

	$(document).on('click', '.remove-address', function () {
		if ($('#shipping_address_div .address-block').length > 1) {
			$(this).closest('.address-block').remove();
		}
	});

	$('#add-address-edit').on('click', function () {
		const block = generateAddressBlock(addressIndex++, false);
		$('#address-container').append(block);
		manageButtonBlock();
	});

	// Edit existing address
	$('.edit-address').on('click', function () {
		$('#add-address-edit').hide();
		$('.address-block').remove();
		const data = $(this).data('address');
		const block = generateAddressBlock(addressIndex++, data);
		$('#address-container').append(block);
		manageButtonBlock();
	});

	// Remove editable block
	$(document).on('click', '.remove-address-edit', function () {
		$('#add-address-edit').show();
		$(this).closest('.address-block').remove();
		manageButtonBlock();
	});

	// Cancel all edits
	$(document).on('click', '.cancel-all', function () {
		$('#add-address-edit').show();
		$('#address-container').empty();
	});
});


$(document).ready(function () {
	const selectedContractId = window.orderData?.contractId || '';
	const selectedShippingId = window.orderData?.shippingId || '';
	const selectedCustomerId = window.orderData?.customerId || '';


	function populateContracts(contracts, selectedId) {
		$('#contract-select').html('<option value="">Choose Contract</option>');

		contracts.forEach(contract => {
			const isSelected = contract.id == selectedId ? 'selected' : '';
			console.log('contract', contract.id, selectedId);

			const productName = contract.product?.name || 'N/A';
			$('#contract-select').append(`
				<option value="${contract.id}" data-qty="${contract.quantity}" ${isSelected}>
					Name: ${productName} - Quantity: ${contract.quantity} - Price: ${contract.price}
				</option>
			`);
		});

		if (selectedId) {
			const selectedOption = $('#contract-select option:selected');
			const qty = selectedOption.data('qty');
			$('#delivered-qty').val(qty);
		}
	}

	function populateShippings(shippings, selectedId = null) {
		$('#shipping-select').html('<option value="">Choose Shipping</option>');

		shippings.forEach(shipping => {
			const isSelected = shipping.id == selectedId ? 'selected' : '';
			$('#shipping-select').append(`
				<option value="${shipping.id}" ${isSelected}>
					${shipping.shipping_address}
				</option>
			`);
		});
	}

	// On customer change
	$('#customer-select').on('change', function () {
		const selected = $(this).find(':selected');
		const contracts = selected.data('contracts') || [];

		const shippings = selected.data('shippings') || [];
		const contractIdToSelect = $(this).val() == selectedCustomerId ? selectedContractId : null;
		const shippingIdToSelect = $(this).val() == selectedCustomerId ? selectedShippingId : null;

		populateContracts(contracts, contractIdToSelect);
		populateShippings(shippings, shippingIdToSelect);

		if (!contractIdToSelect) {
			$('#delivered-qty').val('');
		}
	});

	// On contract change
	$('#contract-select').on('change', function () {
		const qty = $(this).find(':selected').data('qty') || '';
		$('#delivered-qty').val(qty);
	});

	// Trigger on load
	if (selectedCustomerId) {
		$('#customer-select').val(selectedCustomerId).trigger('change');
	}
});

$(document).ready(function () {

	function getIndexFromId(id, base) {
		const match = id.match(new RegExp(`^${base}_(\\d+)$`));
		return match ? match[1] : null;
	}

	function buildSelector(baseId, index) {
		return index !== null ? `#${baseId}_${index}` : `#${baseId}`;
	}

	function updateRoutes(plantId, index = null) {
		const routes = window.routeData || [];
		updateDrivers('', '', index);

		const filteredRoutes = routes.filter(route => route.plant_id == plantId);
		const $routeSelect = $(buildSelector('route_id', index));
		$routeSelect.find('option:not(:first)').remove();

		filteredRoutes.forEach(route => {
			const locations = route.path.replace(/\|/g, ',').split(',').map(loc => loc.trim());
			locations.forEach(location => {
				$routeSelect.append(
					`<option value="${route.id}" data-route-id="${route.id}" data-location="${location}">${route.name} - ${location}</option>`
				);
			});
		});

		const $plant = $(buildSelector('plant_id', index));
		if ($plant.val() && $routeSelect.find('option').length > 1) {
			const firstVal = $routeSelect.find('option:first').val();
			$routeSelect.val(firstVal);
			updateDrivers(firstVal, '', index);
		}
	}

	function updateDrivers(routeId, routePath, index = null) {
		const drivers = window.driverData || [];
		const filteredDrivers = drivers.filter(driver => driver.route_id == routeId && driver.route_path == routePath);
		const $driverSelect = $(buildSelector('driver_id', index));
		$driverSelect.find('option:not(:first)').remove();

		filteredDrivers.forEach(driver => {
			$driverSelect.append(`<option value="${driver.id}" data-driver-id="${driver.id}">${driver.name}</option>`);
		});

		const $route = $(buildSelector('route_id', index));
		if ($route.val() && $driverSelect.find('option').length > 1) {
			$driverSelect.val($driverSelect.find('option:first').val());
		}
	}

	// Static initialization for first/default plant
	const initialPlantId = $('#plant_id').val();
	if (initialPlantId) {
		updateRoutes(initialPlantId, null);
	}

	// Delegated change event for ALL plant_id (static and dynamic)
	$(document).on('change', '[id^=plant_id]', function () {
		const id = $(this).attr('id');
		const index = getIndexFromId(id, 'plant_id');
		const selectedPlantId = $(this).val();
		updateRoutes(selectedPlantId, index);
	});

	// Delegated change event for ALL route_id (static and dynamic)
	$(document).on('change', '[id^=route_id]', function () {
		const id = $(this).attr('id');
		const index = getIndexFromId(id, 'route_id');
		const selectedOption = $(this).find('option:selected');
		const location = selectedOption.data('location');
		const routePath = location;
		const selectedRouteId = $(this).val();

		updateDrivers(selectedRouteId, routePath, index);

		// Update route_path input if exists
		const $routePathInput = $(buildSelector('route_path', index));
		if ($routePathInput.length) {
			$routePathInput.val(routePath);
		}
	});
});


$(document).ready(function () {
	// Toggle the days block based on the frequency
	function toggleDaysBlock() {
		var frequency = $('#frequency').val();
		if (frequency === 'weekly') {
			$('#daysBlock').show();
		} else {
			$('#daysBlock').hide();
		}
	}

	// Update the placeholder for Frequency Count based on the frequency
	function updateFrequencyCountPlaceholder() {
		var frequency = $('#frequency').val();
		var $frequencyCountInput = $('#frequencyCountBlock input');
		switch (frequency) {
			case 'daily':
				$frequencyCountInput.attr('placeholder', 'How many days per week?');
				break;
			case 'alternate_day':
				$frequencyCountInput.attr('placeholder', 'Every other day');
				break;
			case 'weekly':
				$frequencyCountInput.attr('placeholder', 'How many deliveries per week?');
				break;
			case 'twice_per_week':
				$frequencyCountInput.attr('placeholder', 'Twice a week');
				break;
			case 'random':
				$frequencyCountInput.attr('placeholder', 'Random frequency count');
				break;
			default:
				$frequencyCountInput.attr('placeholder', 'Enter Frequency Count');
		}
	}

	// Initialize on page load
	toggleDaysBlock(); // Show/hide days dropdown
	updateFrequencyCountPlaceholder(); // Update placeholder for frequency count

	// When the frequency changes, adjust the form
	$('#frequency').change(function () {
		toggleDaysBlock();
		updateFrequencyCountPlaceholder();
	});
});





