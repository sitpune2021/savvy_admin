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

		$(this).find('.is-invalid').removeClass('is-invalid');
		$(this).find('.invalid-feedback').remove();
		$(this).find('.is-valid').removeClass('is-valid');
		$(this).find('.valid-feedback').remove();

		let formData = new FormData(this);
		let currentMethod = method;
		let Id = $('#id').val();
		console.log($('#id').val());

		if (Id != null && Id != '') {
			if (!actionUrl.includes(`/${Id}`)) {
				actionUrl = actionUrl + '/' + Id;
			}
			currentMethod = 'POST';
			formData.append('_method', 'PUT');
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
					showSuccessAlert(response.message);
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
					if (typeof errorCallback === 'function') {
						showErrorAlert(xhr.responseJSON.error);
					} else {
						showErrorAlert('An error occurred. Please try again.');
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

	document.body.insertAdjacentHTML('beforeend', alertHTML);

	const alert = document.querySelectorAll('.alert-dismissible.alert-danger');
	const thisAlert = alert[alert.length - 1];

	requestAnimationFrame(() => {
		thisAlert.style.transform = 'translateX(0)';
		thisAlert.style.opacity = '1';
	});

	setTimeout(() => {
		thisAlert.style.transform = 'translateX(100%)';
		thisAlert.style.opacity = '0';
		setTimeout(() => thisAlert.remove(), 500); // wait for animation
	}, 5000);
}

function showSuccessAlert(message) {
	const alertHTML = `
		<div class="alert alert-success alert-dismissible fade show"
			 role="alert"
			 style="
				position: fixed;
				top: 80px;
				right: 25px;
				z-index: 1055;
				max-width: 400px;
				opacity: 0;
				transform: translateX(100%);
				transition: transform 0.5s ease-out, opacity 0.5s ease-out;
			 ">
			<i class="ri-check-line me-2"
			   style="font-size: 18px; vertical-align: middle; color:rgb(19, 70, 24);"></i>
			<strong>Success:</strong> ${message}
		
		</div>
	`;

	const tempDiv = document.createElement('div');
	tempDiv.innerHTML = alertHTML;
	const thisAlert = tempDiv.firstElementChild;

	document.body.appendChild(thisAlert);

	// Trigger transition
	requestAnimationFrame(() => {
		thisAlert.style.transform = 'translateX(0)';
		thisAlert.style.opacity = '1';
	});

	// Auto-dismiss after 5 seconds
	setTimeout(() => {
		thisAlert.style.transform = 'translateX(100%)';
		thisAlert.style.opacity = '0';
		setTimeout(() => {
			thisAlert.remove();
			if (window.Laravel?.routeIndex) {
				window.location.href = window.Laravel.routeIndex;
			}
		}, 500);
	}, 5000);
}


handleFormSubmit(
	'#orderForm',
	'/order',
	'POST',
	{},
	function (response) { // success callback
		showSuccessAlert(response.message);
	},
	function (xhr) {
		showErrorAlert('An error occurred. Please try again.');
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#dispensaryForm',
	'/dispensary',
	'POST',
	{},
	function (response) { // success callback
		showSuccessAlert(response.message);
	},
	function (xhr) {
		showErrorAlert('An error occurred. Please try again.');
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#customerForm', // form ID
	'/customer', // URL to send data to
	'POST', // default method
	{},
	function (response) { // success callback
		showSuccessAlert(response.message);
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
		showSuccessAlert(response.message);
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#vendorForm', // form ID
	'/vendor', // URL to send data to
	'POST', // default method
	{},
	function (response) { // success callback
		showSuccessAlert(response.message);
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
		showSuccessAlert(response.message);
	},
	function (xhr) { // error callback
		showErrorAlert('An error occurred. Please try again.');
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#productForm', // form ID
	'/product', // URL to send data to
	'POST', // default method
	{},
	function (response) { // success callback
		showSuccessAlert(response.message);
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
		showSuccessAlert(response.message);
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
	function (response) {
		showSuccessAlert(response.message);
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
	function (response) {
		showSuccessAlert(response.message);
	},
	function (xhr) { // error callback
		console.log('Error occurred:', xhr);
	}
);

handleFormSubmit(
	'#vendorShippingForm', // form ID
	'/customer', // URL to send data to
	'POST', // default method
	{
		subPath: 'vendor-shipping-address',
		method: 'Put',
	},
	function (response) {
		showSuccessAlert(response.message);
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
	let addressContractIndex = 1;

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

	function generateAddressBlock(index, contactIndex, data = {}, isEdit = false) {
		console.log(data.address);

		const routes = window.routeData || [];
		const drivers = window.driverData || [];
		const filteredRoutes = routes.filter(route => route.plant_id == data?.address?.plant_id);
		const filteredDrivers = drivers.filter(driver => driver.route_id == data?.address?.route_id);
		const isVender = window.isvender;
		const deployedSelect = $(`
			<select class="select js-example-basic-single form-control" name="shipping[${index}][machine_deployed]"  ${isVender !== null ? 'disabled' : ''}>
				<option value="Yes" ${data?.address?.machine_deployed === 'Yes' ? 'selected' : ''}>Yes</option>
				<option value="No" ${data?.address?.machine_deployed === 'No' ? 'selected' : ''}>No</option>
			</select>
		`);

		const plantSelect = $(`
			<select class="select js-example-basic-single form-control" name="shipping[${index}][plant_id]" id="plant_id_${index}" ${window.show ? 'disabled' : ''}>
				<option value="">Select Plant</option>
				${window.plants.map(plant => `
					<option value="${plant.id}" ${data?.address?.plant_id === plant.id ? 'selected' : ''}>${plant.name}</option>
				`).join('')}
			</select>
		`);
		const vendorSelect = $(`
			<select class="select js-example-basic-single form-control" name="shipping[${index}][vendor_id]" id="vendor_id_${index}" ${window.show ? 'disabled' : ''}>
				<option value="">Select Vendor</option>
				${window.vendors.map(vendor => `
					<option value="${vendor.id}" ${data?.address?.vendor_id === vendor.id ? 'selected' : ''}>${vendor.user.name}</option>
				`).join('')}
			</select>
		`);

		const routeSelect = $(`
			<select name="shipping[${index}][route_id]" class="select js-example-basic-single form-control" id="route_id_${index}" ${window.show ? 'disabled' : ''}>
				<option value="">Select Route</option>
				${filteredRoutes.map(route => {
			const isSelected = route.id == data?.address?.route_id;
			return `<option value="${route.id}" data-route-id="${route.id}" data-location="${route.path}" ${isSelected ? 'selected' : ''}>${route.name} - ${route.path}</option>`;
		}).join('')
			}
			</select>
		`);

		const driverSelect = $(`
			<select name="shipping[${index}][driver_id]" class="select js-example-basic-single form-control" id="driver_id_${index}" ${window.show ? 'disabled' : ''}>
				<option value="">Select Driver</option>
				${filteredDrivers.map(driver => {
			const isSelected = driver.id == data?.address?.driver_id;
			return `<option value="${driver.id}" ${isSelected ? 'selected' : ''} data-driver-id="${driver.id}" >${driver.name}</option>`;
		}).join('')
			}
			</select>
		`);
		// ${data?.address?.contracts.product_id === product.id ? 'selected' : ''}
		const productSelect = $(`
            <select class="select js-example-basic-single" name="contract[${index}][product_id]"${window.show ? 'disabled' : ''}  ${isVender !== null ? 'disabled' : ''}>
                <option value="">Select Product</option>
				${window.products.map(product => `
					<option value="${product.id}" ${data?.contract?.product_id === product.id ? 'selected' : ''} >${product.name}</option>
				`).join('')}
            </select>
		`)

		const formatFrequency = (str) => {
			return str
				.replace(/_/g, ' ') // Replace underscores with spaces
				.replace(/\b\w/g, char => char.toUpperCase()); // Capitalize first letter of each word
		};

		const frequencies = ['daily', 'alternate_day', 'weekly'];
		const frequencieDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
		const durationType = ['years', 'months', 'weeks', 'days'];

		const frequencySelect = $(`
			<select class="select js-example-basic-single" name="contract[${index}][frequency]" id="frequency_${index}"  ${isVender !== null ? 'disabled' : ''}>
				${frequencies.map(freq => `
					<option value="${freq}" ${data?.contract?.frequency === freq ? 'selected' : ''} >${formatFrequency(freq)}</option>
				`).join('')}
			</select>
		`);

		const selectedDays = data?.contract?.days ? data?.contract?.days.split('|') : [];

		const frequencyDaysSelect = $(`
			<select class="select js-example-basic-single" name="contract[${index}][days][]" multiple  ${isVender !== null ? 'disabled' : ''}>
				${frequencieDays.map(fday => `
					<option value="${fday}" ${selectedDays.includes(fday) ? 'selected' : ''}>${fday.toUpperCase()}</option>
				`).join('')}
			</select>
		`);


		const durationTypeSelect = $(`
			<select class="select js-example-basic-single" name="contract[${index}][duration_type]"  ${isVender !== null ? 'disabled' : ''}>
				${durationType.map(dtyp => `
					<option value="${dtyp}" ${data?.contract?.duration_type === dtyp ? 'selected' : ''}>${dtyp.toUpperCase()}</option>
				`).join('')}
			</select>
		`);

		const block = $(`
			<div class="form-group-item card address-block">
				<div class="card-header d-flex justify-content-between align-items-center add-remove">
					<h5 class="form-title">${isEdit ? (data?.address?.id ? 'Edit' : 'Create') : ''} Shipping Address</h5>
					<button type="button" class="btn btn-sm btn-danger ${isEdit ? 'remove-address-edit' : 'remove-address'}">Remove</button>
				</div>
				<div class="row align-item-center card-body">
					<input type="hidden" name="shipping[${index}][id]" value="${data?.address?.id || ''}">
					<input type="hidden" name="contract[${index}][id]" value="${data?.contract?.id || ''}">
					${isVender === null ? `
					<div class="col-sm-12">
						<div class="input-block mb-3">
							<div class="d-flex align-items-center gap-3 pt-2 pb-2">
								<div class="form-check form-check-outline form-check-dark">
									<input class="form-check-input type-checkbox checkbox_${index}" type="checkbox"
										name="shipping[${index}][type]" id="typeLocal_${index}" value="local" data-index="${index}"  ${(data?.address?.type === 'local' || !data?.address?.type) ? 'checked' : ''}>
									<label class="form-check-label" for="typeLocal_${index}">Local</label>
								</div>
								<div class="form-check form-check-outline form-check-dark">
									<input class="form-check-input type-checkbox checkbox_${index}" type="checkbox"
										name="shipping[${index}][type]" id="typePanIndia_${index}" value="pan_india" data-index="${index}" ${data?.address?.type === 'pan_india' ? 'checked' : ''}>
									<label class="form-check-label" for="typePanIndia_${index}">Pan India</label>
								</div>
							</div>
						</div>
					</div>
					` : ``}
					<div class="col-sm-12">
						<div class="input-block mb-3">
							<label>Address</label>
							<input name="shipping[${index}][shipping_address]" type="text" class="form-control"
								placeholder="Enter Shipping Address" value="${data?.address?.shipping_address || ''}" ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>Country</label>
							<input name="shipping[${index}][shipping_country]" type="text" class="form-control"
								placeholder="Enter Shipping Country" value="${data?.address?.shipping_country || ''}" ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>State</label>
							<input name="shipping[${index}][shipping_state]" type="text" class="form-control"
								placeholder="Enter Shipping State" value="${data?.address?.shipping_state || ''}" ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>City</label>
							<input name="shipping[${index}][shipping_city]" type="text" class="form-control"
								placeholder="Enter Shipping City" value="${data?.address?.shipping_city || ''}" ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>Pin Code</label>
							<input name="shipping[${index}][shipping_pincode]" type="number" class="form-control"
								placeholder="Enter Shipping Pin Code" value="${data?.address?.shipping_pincode || ''}" ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>
					${isVender === null ? `
					<div class="col-lg-4 col-md-6 col-sm-12" id="vendor_select_${index}">
						<div class="input-block mb-3 vendor-container">
							<label>vendor</label>
						</div>
					</div>
					` : ``}
					<div class="col-lg-4 col-md-6 col-sm-12" id="plant_select_${index}">
						<div class="input-block mb-3 plant-container">
							<label>Plant</label>
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-12" id="route_select_${index}">
						<div class="input-block mb-3 route-container">
							<label>Routes</label>
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-12" id="driver_select_${index}">
						<div class="input-block mb-3 driver-container">
							<label>Drivers</label>
						</div>
					</div>
					<div class="col-12" id="shipping_contact_div_${index}">
					${data?.address?.id && data?.address?.contacts?.length > 0 ? '' : `
                        <div class="row align-item-center ">
							<input name="shipping[${index}][shipping_contacts][${contactIndex}][id]" type="hidden" value="" >
							<div class="col-lg-5 col-md-6 col-sm-12">
								<div class="input-block mb-3">
									<label>Name</label>
									<input name="shipping[${index}][shipping_contacts][${contactIndex}][name]" type="text" class="form-control"
										placeholder="Enter Name" value="${data?.address?.name || ''}" ${isVender !== null ? 'disabled' : ''}>
								</div>
							</div>
							<div class="col-lg-5 col-md-6 col-sm-12">
								<div class="input-block mb-3">
									<label>Mobile No</label>
									<input name="shipping[${index}][shipping_contacts][${contactIndex}][phone]" type="text" class="form-control"
										placeholder="Enter Mobile No" value="${data?.address?.phone || ''}" ${isVender !== null ? 'disabled' : ''}>
								</div>
							</div>
							<div class="col-lg-2 col-md-6 col-sm-12 d-flex align-items-center justify-content-center">
								<button type="button" class="btn btn-sm btn-success"
									id="add-address-contacts_${index}">
									+ Add Contact
								</button>
							</div>
						</div>
						`}
						 ${data?.address?.contacts?.length > 0 ? data?.address?.contacts?.map((contact, contactIndex) => `
							<div class="row align-items-center address-contact-block" >
								<input name="shipping[${index}][shipping_contacts][${contactIndex}][id]" type="hidden" value="${contact.id}">
								<div class="col-lg-5 col-md-6 col-sm-12">
									<div class="input-block mb-3">
										<label>Name</label>
										<input name="shipping[${index}][shipping_contacts][${contactIndex}][name]" type="text" class="form-control"
											placeholder="Enter Name" value="${contact.name || ''}" ${isVender !== null ? 'disabled' : ''}>
									</div>
								</div>
								<div class="col-lg-5 col-md-6 col-sm-12">
									<div class="input-block mb-3">
										<label>Mobile No</label>
										<input name="shipping[${index}][shipping_contacts][${contactIndex}][phone]" type="text" class="form-control"
											placeholder="Enter Mobile No" value="${contact.phone || ''}" ${isVender !== null ? 'disabled' : ''}>
									</div>
								</div>
								${isVender === null ? `
								${contactIndex === 0 ? `
									<div class="col-lg-2 col-md-6 col-sm-12 d-flex align-items-center justify-content-center">
										<button type="button" class="btn btn-sm btn-success" id="add-address-contacts_${index}">
											+ Add Contact
										</button>
									</div>
								` : `<div
                                        class="col-lg-2 col-md-6 col-sm-12 d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm btn-danger" id="remove-address-contacts" data-index="${index}" data-is-delete="${contact.id}">
                                            - remove Contact
                                        </button>
                                    </div>`}
									` : ''}
							</div>
						`).join('') : ''}
					</div>
					<div class="col-lg-4 col-md-6 col-sm-12" id="product_select">
                        <div class="input-block mb-3 product-container">
                            <label>Product</label>
                        </div>
                    </div>
					<div class="col-lg-4 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>Stock</label>
							<input name="contract[${index}][quantity]" type="number" class="form-control" placeholder="Enter Stock" value="${data?.contract?.quantity || '1'}"  ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>Price /-</label>
							<input name="contract[${index}][price]" type="number" class="form-control" placeholder="Enter Price"
								value="${data?.contract?.price || '1'}"  ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>

					<div class="col-lg-4 col-md-6 col-sm-12" id="frequency_select">
						<div class="input-block mb-3 frequency-container">
							<label>Delivery Frequency</label>
						</div>
					</div>

					<div class="col-lg-4 col-md-6 col-sm-12" id="frequency_count_${index}">
						<div class="input-block mb-3">
							<label>Frequency Count</label>
							<input name="contract[${index}][frequency_count]" type="number" class="form-control" value="${data?.contract?.frequency_count || '1'}"  ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>

					<div class="col-lg-4 col-md-6 col-sm-12" id="days_select_${index}">
						<div class="input-block mb-3 days-container">
							<label>Delivery Day</label>
						</div>
					</div>

					<div class="col-lg-4 col-md-6 col-sm-12">
						<div class="input-block mb-3">
							<label>Duration</label>
							<input name="contract[${index}][duration]" type="number" class="form-control" placeholder="Enter Duration"
								value="${data?.contract?.duration || '1'}"  ${isVender !== null ? 'disabled' : ''}>
						</div>
					</div>

					<div class="col-lg-4 col-md-6 col-sm-12" id="duration_type_select">
						<div class="input-block mb-3 duration-type-container">
							<label>Duration Type</label>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-6 col-sm-12" id="machine_select">
						<div class="input-block mb-3 deployed-container">
							<label>Deployed</label>
						</div>
					</div>

				</div>
			</div>
		`);

		block.find('.deployed-container').append(deployedSelect);
		block.find('.plant-container').append(plantSelect);
		block.find('.vendor-container').append(vendorSelect);
		block.find('.route-container').append(routeSelect);
		block.find('.driver-container').append(driverSelect);
		block.find('.product-container').append(productSelect);
		block.find('.frequency-container').append(frequencySelect);
		block.find('.days-container').append(frequencyDaysSelect);
		block.find('.duration-type-container').append(durationTypeSelect);
		block.find('select.select').select2();

		if (frequencySelect.val() === 'weekly') {
			block.find(`#days_select_${index}`).show();
		} else {
			block.find(`#days_select_${index}`).hide();
		}
		const selectedType = block.find(`.checkbox_${index}:checked`).val();

		if (selectedType === 'pan_india') {
			block.find(`#vendor_select_${index}`).show();
			block.find(`#plant_select_${index}, #route_select_${index}, #driver_select_${index}`).hide();
		} else if (selectedType === 'local') {
			block.find(`#vendor_select_${index}`).hide();
			block.find(`#plant_select_${index}, #route_select_${index}, #driver_select_${index}`).show();
		}

		return block;
	}

	function generateAddressContactBlock(index, addressIndex) {
		const addressBlock = $(`<div class="row align-item-center address-contact-block">
								   <input name="shipping[${addressIndex}][shipping_contacts][${index}][id]" type="hidden" value="" >
                                    <div class="col-lg-5 col-md-6 col-sm-12">
                                        <div class="input-block mb-3">
                                            <label>Name</label>
                                            <input name="shipping[${addressIndex}][shipping_contacts][${index}][name]" type="text"
                                                class="form-control" placeholder="Enter Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-6 col-sm-12">
                                        <div class="input-block mb-3">
                                            <label>Mobile No</label>
                                            <input name="shipping[${addressIndex}][shipping_contacts][${index}][phone]"
                                                type="text" class="form-control" placeholder="Enter Mobile No">
                                        </div>
                                    </div>
                                    <div
                                        class="col-lg-2 col-md-6 col-sm-12 d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm btn-danger" id="remove-address-contacts" data-index="${addressIndex}">
                                            - remove Contact
                                        </button>
                                    </div>
                                </div>`);
		return addressBlock;
	}

	$('#add-address').on('click', function () {
		const block = generateAddressBlock(addressIndex++, addressContractIndex++, {}, window.CustomerExists);
		if (window.CustomerExists) {
			$('#address-container').append(block);
			manageButtonBlock();
		} else {
			$('#shipping_address_div').append(block);
		}
	});


	$(document).on('click', '[id^="add-address-contacts"]', function () {
		let $button = $(this);
		let buttonId = $button.attr('id');
		let suffix = buttonId.replace('add-address-contacts', ''); // e.g. '', '_1', '_2'
		let shippingDivId = 'shipping_contact_div' + suffix;

		let parentindex = 0;
		$('#' + shippingDivId)
			.find('input[name*="[name]"]')
			.each(function () {
				const nameAttr = $(this).attr('name');
				const match = nameAttr.match(/^shipping\[(\d+)]/);
				if (match) {
					parentindex = match[1]; // Use match[1] to get the index
				}
			});

		const contactBlock = generateAddressContactBlock(addressContractIndex++, parentindex, {}, false);
		$('#' + shippingDivId).append(contactBlock);
	});


	$(document).on('click', '.remove-address', function () {
		if ($('#shipping_address_div .address-block').length > 1) {
			$(this).closest('.address-block').remove();
		}
	});

	$(document).on('click', '#remove-address-contacts', function () {
		$(this).closest('.address-contact-block').remove();
	});



	$('.edit-address').on('click', function () {
		$('#add-address').hide();
		$('.address-block').remove();
		const data = {
			address: {
				...$(this).data('address'),
			},
			contract: $(this).data('contract')
		};
		const block = generateAddressBlock(addressIndex++, addressContractIndex++, data, true);
		$('#address-container').append(block);
		manageButtonBlock();
	});

	$(document).on('click', '.remove-address-edit', function () {
		$('#add-address').show();
		$(this).closest('.address-block').remove();
		manageButtonBlock();
	});

	$(document).on('click', '.cancel-all', function () {
		$('#add-address-edit').show();
		$('#address-container').empty();
	});

	function toggleShippingFields(index) {
		const selectedType = $(`.checkbox_${index}:checked`).val();

		if (selectedType === 'pan_india') {
			$(`#vendor_select_${index}`).show();
			$(`#plant_select_${index}, #route_select_${index}, #driver_select_${index}`).hide();
		} else if (selectedType === 'local') {
			$(`#vendor_select_${index}`).hide();
			$(`#plant_select_${index}, #route_select_${index}, #driver_select_${index}`).show();
		}
	}

	// Handle checkbox changes for each index group
	$(document).on('change', '.type-checkbox', function () {
		const index = $(this).data('index');

		// Allow only one checked per index
		$(`.checkbox_${index}`).not(this).prop('checked', false);

		// If none checked after this action, force this one to remain checked
		if (!$(`.checkbox_${index}:checked`).length) {
			$(this).prop('checked', true);
		}

		toggleShippingFields(index);
	});

	// Initialize on page load
	$('.type-checkbox:checked').each(function () {
		const index = $(this).data('index');
		toggleShippingFields(index);
	});
});


$(document).ready(function () {
	const selectedShippingId = window.orderData?.shippingId || '';
	const selectedCustomerId = window.orderData?.customerId || '';
	const selectedDeliveryId = window.orderData?.deliveryId || '';

	function populateShippings(shippings, selectedId = null) {
		$('#shipping-select').empty();
		shippings.forEach(shipping => {
			const isSelected = shipping.id == selectedId ? 'selected' : '';
			$('#shipping-select').append(`
				<option value="${shipping.id}" ${isSelected}>
					${shipping.shipping_address}
				</option>
			`);
		});
	}

	$('#customer-select').on('change', function () {
		const selected = $(this).find(':selected');
		const shippings = selected.data('shippings') || [];
		const shippingIdToSelect = $(this).val() == selectedCustomerId ? selectedShippingId : null;
		populateShippings(shippings, shippingIdToSelect);
	});

	if (selectedCustomerId) {
		$('#customer-select').val(selectedCustomerId).trigger('change');
	}
	if (selectedDeliveryId) {
		$('#delivered_qty').val(selectedDeliveryId);
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
			if (window.locationData) {
				const locations = route.path.replace(/\|/g, ',').split(',').map(loc => loc.trim());
				locations.forEach(location => {
					const isSelected = window.selectedRouteId == route.id && window.selectedRoutePath == location;
					const selectedAttr = isSelected ? 'selected' : '';
					$routeSelect.append(
						`<option value="${route.id}" data-route-id="${route.id}" data-location="${location}" ${selectedAttr}>${route.name} - ${location}</option>`
					);
				});
			} else {
				const isSelected = window.selectedRouteId == route.id;
				const selectedAttr = isSelected ? 'selected' : '';
				$routeSelect.append(
					`<option value="${route.id}" data-route-id="${route.id}" data-location="${route.path}" ${selectedAttr}>${route.name} - ${route.path}</option>`
				);
			}
		});
		const $plant = $(buildSelector('plant_id', index));
		if (!window.selectedRouteId && $plant.val() && $routeSelect.find('option').length > 1) {
			const firstVal = $routeSelect.find('option:first').val();
			$routeSelect.val(firstVal);
			updateDrivers(firstVal, '', index);
		} else if (window.selectedRouteId) {
			const selectedOption = $routeSelect.find(`option:selected`);
			const routePath = selectedOption.data('location') || '';
			updateDrivers(window.selectedRouteId, routePath, index);
			const $routePathInput = $(buildSelector('route_path', index));
			if ($routePathInput.length) {
				$routePathInput.val(routePath);
			}
		}
	}

	function updateDrivers(routeId, routePath, index = null) {
		const drivers = window.driverData || [];
		let filteredDrivers = [];

		if (window.locationData) {
			filteredDrivers = drivers.filter(driver => {
				const pathSegments = driver.route_path
					?.toLowerCase()
					.split(/[\|,]/) // Split by '|' or ',' or both
					.map(segment => segment.trim()); // Trim extra spaces

				return (
					driver.route_id == routeId &&
					pathSegments?.includes(routePath.toLowerCase())
				);
			});
		} else {
			filteredDrivers = drivers.filter(driver => driver.route_id == routeId);
		}


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

	const initialPlantId = $('#plant_id').val();
	if (initialPlantId) {
		updateRoutes(initialPlantId, null);
	}

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
	function getSuffixFromId(id) {
		// If ID is like "frequency" return "", if "frequency_1" return "_1"
		const match = id.match(/^frequency(?:_(\d+))?$/);
		return match && match[1] !== undefined ? '_' + match[1] : '';
	}

	function toggleDaysBlock($frequencySelect) {
		const frequency = $frequencySelect.val();
		const suffix = getSuffixFromId($frequencySelect.attr('id'));
		const $daysBlock = $('#days_select' + suffix);

		if ($daysBlock.length) {
			if (frequency === 'weekly') {
				$daysBlock.show();
			} else {
				$daysBlock.hide();
			}
		}
	}

	function updateFrequencyCountPlaceholder($frequencySelect) {
		const frequency = $frequencySelect.val();
		const suffix = getSuffixFromId($frequencySelect.attr('id'));
		const $frequencyCountInput = $('#frequency_count' + suffix + ' input');

		if ($frequencyCountInput.length) {
			let placeholderText = 'Enter Frequency Count';
			switch (frequency) {
				case 'daily':
					placeholderText = 'How many days per week?';
					break;
				case 'alternate_day':
					placeholderText = 'Every other day';
					break;
				case 'weekly':
					placeholderText = 'How many deliveries per week?';
					break;
			}
			$frequencyCountInput.attr('placeholder', placeholderText);
		}
	}

	// Initialize existing rows on page load
	$('[id^="frequency"]').each(function () {
		toggleDaysBlock($(this));
		updateFrequencyCountPlaceholder($(this));
	});

	// Handle changes on all frequency selects (also supports dynamic rows)
	$(document).on('change', '[id^="frequency"]', function () {
		toggleDaysBlock($(this));
		updateFrequencyCountPlaceholder($(this));
	});
});



function toggleSelect() {
	let selectedValue = $('.type-checkbox:checked').val();
	if (!selectedValue) {
		const firstCheckbox = $('.type-checkbox').first();
		firstCheckbox.prop('checked', true);
		selectedValue = firstCheckbox.val();
	}

	if (selectedValue === 'pan_india') {
		$('#vendor_select').show();
	} else {
		$('#vendor_select').hide();
		$('#vendor_id').val(null).trigger('change'); // Reset Select2 value
	}
}

$(document).ready(function () {
	$(document).on('change', '.type-checkbox', function () {
		$('.type-checkbox').not(this).prop('checked', false);

		if (!$('.type-checkbox:checked').length) {
			$(this).prop('checked', true);
		}

		toggleSelect();
	});

	toggleSelect();
});




function toggleCheckBox() {
	const selectedValue = $('.checkbox-home:checked').val();
	localStorage.setItem('val', selectedValue);
	window.location.href = `/?value=${selectedValue}`;
}

$(document).ready(function () {
	const savedVal = localStorage.getItem('val');

	if (savedVal) {
		$(`.checkbox-home[value="${savedVal}"]`).prop('checked', true);
	} else {
		$('.checkbox-home').first().prop('checked', true);
	}

	$(document).on('change', '.checkbox-home', function () {
		$('.checkbox-home').not(this).prop('checked', false);

		if (!$('.checkbox-home:checked').length) {
			$(this).prop('checked', true);
		}

		toggleCheckBox();
	});
});













