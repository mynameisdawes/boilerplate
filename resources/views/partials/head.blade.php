<head>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            window.dataLayer.push(arguments);
        }
    </script>
    @if (config('app.gtm'))
    <!-- Google Tag Manager Consent Initialization -->
    <!-- Wait for consent to drop analytics cookies -->
    <script>
        gtag("consent", "default", {
            ad_storage: "denied",
            analytics_storage: "denied",
            functionality_storage: "denied",
            personalization_storage: "denied",
            security_storage: "granted",
            wait_for_update: 2000,
        });
        gtag("set", "ads_data_redaction", true);
    </script>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ config('app.gtm') }}');</script>
    <!-- End Google Tag Manager -->
    @endif

    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />

    <title>{{ config('app.name') }}@hasSection('title') | @yield('title')@endif</title>
    @hasSection('meta_description')
    <meta name="description" content="@yield('meta_description')" />
    @elseif (config('app.description'))
    <meta name="description" content="{{ config('app.description') }}" />
    @endif
    @hasSection('meta_title')
    <meta property="og:title" content="{{ config('app.name') }} | @yield('meta_title')" />
    @else
    <meta property="og:title" content="{{ config('app.name') }}@hasSection('title') | @yield('title')@endif" />
    @endif
    @hasSection('meta_description')
    <meta property="og:description" content="@yield('meta_description')" />
    @elseif (config('app.description'))
    <meta property="og:description" content="{{ config('app.description') }}" />
    @endif
    {{-- <meta property="og:type" content="" /> --}}
    <meta property="og:url" content="{{ url()->full() }}" />
    @hasSection('meta_image')
    <meta property="og:image" content="@yield('meta_image')" />
    <meta property="og:image:alt" content="@yield('meta_image')" />
    <meta name="twitter:image" content="@yield('meta_image')" />
    <meta name="twitter:card" content="summary_large_image" />
    @endif

    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="view-transition" content="same-origin" />
    <meta name="format-detection" content="telephone=no" />

    <base href="{{ route('base') }}" />
    <link rel="canonical" href="@yield('canonical_url', url()->full())" />
    <meta name="theme-color" content="#FFFFFF" />

    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
    <link rel="icon" type="{{ app('App\Http\Controllers\AssetController')->favicon_type() }}" href="{{ route('favicon') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link href="{{ url('dist/style.css?v=' . config('app.static_version')) }}" rel="stylesheet">

    <?php
    $override_css = resource_path('assets/style.css');
    if (file_exists($override_css)) {
        echo "<style>\n";
            require_once $override_css;
        echo "\n</style>";
    }
    ?>
</head>