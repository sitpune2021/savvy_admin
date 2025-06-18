 <div class="col-xl-12">
     {{-- ['region' => $key, 'data' => $regionData] --}}
     <h4 class="fs-16 fw-bold pb-1">{{ ucwords(str_replace('_', ' ', $region))}}</h4>
     <div class="row">
         @include('components.dashbordCards', [ ...$regionData, 'key' => $key])
     </div>
 </div>
