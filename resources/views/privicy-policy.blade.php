<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
     data-theme="default"
    data-theme-colors="default">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.seo', [
        'title' => $title ?? null,
        'description' => $description ?? null,
        'keywords' => $keywords ?? null,
        'image' => $image ?? null,
    ])
    <style>
        .fixed-alert {
            left: 50%;
            width: auto;
            max-width: 80%;
            z-index: 1055;
        }
    </style>
    @stack('styles')
    @include('layouts.partials.head')
</head>

<body>
    <div id="layout-wrapper">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="card">
                                <div class="bg-warning-subtle position-relative">
                                    <div class="card-body p-5">
                                        <div class="text-center">
                                            <h3>Privacy Policy</h3>
                                            <p class="mb-0 text-muted">Last update: 22 April, 2025</p>
                                        </div>
                                    </div>
                                    <div class="shape">
                                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                                            width="1440" height="60" preserveAspectRatio="none" viewBox="0 0 1440 60">
                                            <g mask="url(&quot;#SvgjsMask1001&quot;)" fill="none">
                                                <path
                                                    d="M 0,4 C 144,13 432,48 720,49 C 1008,50 1296,17 1440,9L1440 60L0 60z"
                                                    style="fill: var(--vz-secondary-bg);"></path>
                                            </g>
                                            <defs>
                                                <mask id="SvgjsMask1001">
                                                    <rect width="1440" height="60" fill="#ffffff"></rect>
                                                </mask>
                                            </defs>
                                        </svg>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <i data-feather="check-circle"
                                                class="text-success icon-dual-success icon-xs"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5>Privacy Policy for [Insert Driver App Name]</h5>
                                            <p class="text-muted">Effective Date: 22 April, 2025</p>
                                            <p class="text-muted">This Privacy Policy explains how we collect, use, and protect your information when you use our Driver Application ("App"). This App is intended solely for internal use by our delivery personnel to manage and complete assigned delivery tasks.</p>
                                            <p class="text-muted"><b>1. Information We Collect</b></p>
                                            <ul class="text-muted">
                                                <li>
                                                    <p><strong>Mobile Number:</strong> Used for authentication and communication.</p>
                                                </li>
                                                <li>
                                                    <p><strong>Location Data (GPS):</strong> Collected in real-time to track delivery routes, assign orders based on location, and ensure efficient service.</p>
                                                </li>
                                            </ul>
                                            <p class="text-muted"><b>2. How We Use Your Information</b></p>
                                            <ul class="text-muted">
                                                <li>
                                                    <p>The information collected is used strictly for internal operational purposes, including verifying your identity, assigning and tracking delivery orders, improving logistics, ensuring safety, and enhancing service.</p>
                                                </li>
                                                <li>
                                                    <p>We do not use your information for advertising or marketing purposes.</p>
                                                </li>
                                            </ul>
                                            <p class="text-muted"><b>3. Data Sharing</b></p>
                                            <p class="text-muted">We do not sell or share your personal information with third parties. Your data is accessible only to authorized personnel within our organization for the purpose of managing deliveries and app functionality.</p>
                                            <p class="text-muted"><b>4. Data Security</b></p>
                                            <p class="text-muted">We take appropriate security measures to protect your personal data from unauthorized access, disclosure, alteration, or destruction. These include encryption, access controls, and secure data storage practices.</p>
                                            <p class="text-muted"><b>5. Location Permission</b></p>
                                            <p class="text-muted">This App requires access to your device’s location services. Your real-time location is used solely to support delivery operations. Location data is only collected when the App is active or running in the background during a delivery task.</p>
                                            <p class="text-muted"><b>6. Data Retention</b></p>
                                            <p class="text-muted">We retain your information only as long as necessary for operational purposes or as required by law. Once no longer needed, your data will be securely deleted.</p>
                                            <p class="text-muted"><b>7. Your Rights</b></p>
                                            <p class="text-muted">As a user, you have the right to access, update, or request deletion of your personal information. For any such requests, please contact us using the details below.</p>
                                            <p class="text-muted"><b>8. Contact Us</b></p>
                                            <p class="text-muted">If you have any questions or concerns about this Privacy Policy, please contact us at:</p>
                                            <ul class="text-muted">
                                                <li><strong>Company Name:</strong> Swayambhoo infotech</li>
                                                <li><strong>Email:</strong> swayambhooinfotech@gmail.com</li>
                                                <li><strong>Phone:</strong> 9090909090</li>
                                            </ul>
                                            <p class="text-muted"><b>9. Changes to This Policy</b></p>
                                            <p class="text-muted">We may update this Privacy Policy from time to time. Any changes will be notified within the App or via other means.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('/assets/js/plugins.js') }}"></script>
    <script src="{{ asset('/assets/js/app.js') }}"></script>

</body>

</html>
