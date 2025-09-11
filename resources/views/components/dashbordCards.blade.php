 @php
     $orderIsDown = $orderChange < 0;
     $orderChangeClass = $orderIsDown ? 'text-danger' : 'text-success';
     $orderIcon = $orderIsDown ? 'ri-arrow-right-down-line' : 'ri-arrow-right-up-line';

     $customerIsDown = $customerChange < 0;
     $customerChangeClass = $customerIsDown ? 'text-danger' : 'text-success';
     $customerIcon = $customerIsDown ? 'ri-arrow-right-down-line' : 'ri-arrow-right-up-line';
 @endphp

 <div class="col-xl-3 col-md-6">
     <div class="card card-animate">
         <div class="card-body">
             <div class="d-flex align-items-center">
                 <div class="flex-grow-1 overflow-hidden">
                     <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                         Orders
                     </p>
                 </div>
                 <div class="flex-shrink-0">
                     <h5 class="{{ $orderChangeClass }} fs-14 mb-0">
                         <i class="{{ $orderIcon }} fs-13 align-middle"></i>
                         {{ $orderChange }} %
                     </h5>
                 </div>
             </div>
             <div class="d-flex align-items-end justify-content-between mt-4">
                 <div>
                     <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                         <span class="counter-value" data-target="{{ $thisMonthOrders }}">0</span>
                     </h4>
                     <a href="{{ url('order') }}" class="d-flex align-items-center gap-2">
                         <p class="fs-16 mb-0 text-muted"><i
                                 class="mdi mdi-circle fs-14 align-middle text-success me-1"></i><span
                                 class="counter-value" data-target="{{ $thisMonthCompletedOrders }}">0</span> </p>
                         <p class="fs-16 mb-0 text-muted"><i
                                 class="mdi mdi-circle fs-14 align-middle text-danger me-1"></i><span
                                 class="counter-value" data-target="{{ $thisMonthPendingOrders }}">0</span>
                         </p>
                         <p class="fs-16 mb-0 text-muted"><i
                                 class="mdi mdi-circle fs-14 align-middle text-warning me-1"></i><span
                                 class="counter-value" data-target="{{ $thisMonthInProgressOrders }}">0</span>
                         </p>
                     </a>
                 </div>
                 <div class="avatar-sm flex-shrink-0">
                     <span class="avatar-title bg-info-subtle rounded fs-3">
                         <i class="bx bx-shopping-bag text-info"></i>
                     </span>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <div class="col-xl-3 col-md-6">
     <div class="card card-animate">
         <div class="card-body">
             <div class="d-flex align-items-center">
                 <div class="flex-grow-1 overflow-hidden">
                     <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                         Today Orders
                     </p>
                 </div>
             </div>
             <div class="d-flex align-items-end justify-content-between mt-4">
                 <div>
                     <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                         <span class="counter-value" data-target="{{ $todayOrders }}">0</span>
                     </h4>
                     <a href="{{ url('order') }}" class="d-flex align-items-center gap-2">
                         <p class="fs-16 mb-0 text-muted"><i
                                 class="mdi mdi-circle fs-14 align-middle text-success me-1"></i><span
                                 class="counter-value" data-target="{{ $todayCompletedOrders }}">0</span> </p>
                         <p class="fs-16 mb-0 text-muted"><i
                                 class="mdi mdi-circle fs-14 align-middle text-danger me-1"></i><span
                                 class="counter-value" data-target="{{ $todayPendingOrders }}">0</span>
                         </p>
                         <p class="fs-16 mb-0 text-muted"><i
                                 class="mdi mdi-circle fs-14 align-middle text-warning me-1"></i><span
                                 class="counter-value" data-target="{{ $todayInProgressOrders }}">0</span>
                         </p>
                     </a>
                 </div>
                 <div class="avatar-sm flex-shrink-0">
                     <span class="avatar-title bg-info-subtle rounded fs-3">
                         <i class="bx bx-shopping-bag text-info"></i>
                     </span>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <div class="col-xl-3 col-md-6">
     <div class="card card-animate bg-danger">
         <div class="card-body">
             <div class="d-flex align-items-center">
                 <div class="flex-grow-1 overflow-hidden">
                     <p class="text-uppercase fw-medium text-white text-truncate mb-0">
                         Yesterday Orders
                     </p>
                 </div>
             </div>
             <div class="d-flex align-items-end justify-content-between mt-4">
                 <div>
                     <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-white">
                         <span class="counter-value" data-target="{{ $yesterdayPendingOrders }}">0</span>
                     </h4>

                     <a href="#yesterdayPendingOrders" class="text-decoration-underline text-white fetch-pending-orders"@if(isset($key))
    data-key="{{ $key }}"
@endif
>
                         <span class="counter-value" data-target="{{ $allPendingOrdersCount }}">0</span>
                         view pending orders
                     </a>
                 </div>
                 <div class="avatar-sm flex-shrink-0">
                     <span class="avatar-title  bg-white bg-opacity-25 rounded fs-3">
                         <i class="bx bx-shopping-bag text-white"></i>
                     </span>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <div class="col-xl-3 col-md-6">
     <div class="card card-animate">
         <div class="card-body">
             <div class="d-flex align-items-center">
                 <div class="flex-grow-1 overflow-hidden">
                     <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                         Customers
                     </p>
                 </div>
                 <div class="flex-shrink-0">
                     <h5 class="{{ $customerChangeClass }} fs-14 mb-0">
                         <i class="{{ $customerIcon }} fs-13 align-middle"></i>
                         {{ $customerChange }} %
                     </h5>
                 </div>
             </div>
             <div class="d-flex align-items-end justify-content-between mt-4">
                 <div>
                     <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                         <span class="counter-value" data-target="{{ $thisMonthCustomers }}">0</span>
                     </h4>
                     <a href="{{ url('customer') }}" class="text-decoration-underline">See
                         details</a>
                 </div>
                 <div class="avatar-sm flex-shrink-0">
                     <span class="avatar-title bg-warning-subtle rounded fs-3">
                         <i class="bx bx-user-circle text-warning"></i>
                     </span>
                 </div>
             </div>
         </div>
     </div>
 </div>
