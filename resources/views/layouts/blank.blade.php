<!doctype html>
<html class="no-js" lang="{{ app()->getLocale() }}">
    @include('partials.head')
    <body>
        @if (config('app.gtm'))
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('app.gtm') }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        @endif
        <div class="document__wrapper" v-cloak>
            <main class="document__content" role="main" aria-label="Document Content">
                @yield('content')
            </main>
        </div>
        @include('partials.config')
        @if (config('shop.enabled') === true && request()->route() && in_array(request()->route()->getName(), ['dashboard.onecrm.index', 'shop.checkout.index', 'checkout_quote.checkout']))
            @if (config('paypal.enabled') === true)
            <script src="https://www.paypalobjects.com/api/checkout.js"></script>
            @endif
        @endif
        <script defer src="{{ url('dist/bundle.js?v=' . config('app.static_version')) }}"></script>
    </body>
</html>