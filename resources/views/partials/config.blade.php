@php
if (!function_exists('convertToBytes')) {
    function convertToBytes($size) {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;

        switch ($unit) {
            case 'g': return $value * 1024 * 1024 * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'k': return $value * 1024;
            default: return $value;
        }
    }
}

$upload_max_filesize_bytes = convertToBytes(ini_get('upload_max_filesize'));

$navigation_items = [];

if (class_exists('Vektor\CMS\Services\NavigationService')) {
    $navigation_service = new Vektor\CMS\Services\NavigationService();
    $header_navigation = $navigation_service->fetch('header');
    if ($header_navigation && $header_navigation->items->count() > 0) {
        $navigation_items = $header_navigation->items->toArray();
    }
}

if (empty($navigation_items)) {
    if (config('app.external.url') && config('app.external.label')) {
        $navigation_items[] = [ 'title' => config('app.external.label'), 'href' => config('app.external.url'), 'attributes' => ['target' => '_blank'] ];
    }
    if (config('shop.only') === false) {
        if (config('shop.enabled') === true && config('shop.as_base') === false) {
            $navigation_items[] = [ 'title' => 'Shop', 'href' => route('shop.product.index') ];
        } else {
            $navigation_items[] = [ 'title' => 'About', 'href' => route('about') ];
        }
        $navigation_items[] = [ 'title' => 'Tabs', 'href' => route('tabs') ];
        $navigation_items[] = [ 'title' => 'Article', 'href' => route('article') ];
        $navigation_items[] = [ 'title' => 'Map', 'href' => route('map') ];
        $navigation_items[] = [ 'title' => 'Contact', 'href' => route('contact') ];
    }
}

$routes = Route::getRoutes();
$named_api_routes = [];

foreach ($routes as $route) {
    if (preg_match('/^\/?api/', $route->getPrefix())) {
        $route_name = $route->getName();

        if ($route_name) {
            $route_url = $route->uri();
            $route_parameters = $route->parameterNames();

            if (!empty($route_parameters)) {
                foreach ($route_parameters as $parameter_name) {
                    $route_url = str_replace(['{' . $parameter_name . '}', '{' . $parameter_name . '?}'], '', $route_url);
                }
            }

            $route_url = trim($route_url, '/');
            $named_api_routes[$route_name] = url($route_url);
        }
    }
}
@endphp

<script id="_configParams">
const params = {
'env': '<?php echo config('app.env'); ?>',
'php.upload_max_filesize_bytes': '<?php echo $upload_max_filesize_bytes; ?>',
'base': '{{ route('base') }}',
'user.is_logged_in': <?php echo auth()->check() ? 'true' : 'false'; ?>,
@if (!empty($named_api_routes))
@php
ksort($named_api_routes);
@endphp
@foreach ($named_api_routes as $named_api_route_name => $named_api_route_url)
{!! "'" . $named_api_route_name . "': '" . $named_api_route_url . "'," !!}
@endforeach
@endif
'api.data': '<?php echo config('api.encryption_key'); ?>',
'navigation_items': {!! json_encode($navigation_items) !!},
'app.account.enabled': <?php echo config('app.account.enabled') === true ? 'true' : 'false'; ?>,
'app.search.enabled': <?php echo config('app.search.enabled') === true ? 'true' : 'false'; ?>,
'app.color_scheme.enabled': <?php echo config('app.color_scheme.enabled') === true ? 'true' : 'false'; ?>,
'app.marketing.enabled': <?php echo config('marketing.enabled') === true && config('marketing.mailchimp.enabled') === true && config('marketing.mailchimp.list_id') ? 'true' : 'false'; ?>,
'onecrm.enabled': <?php echo config('onecrm.enabled') === true ? 'true' : 'false'; ?>,
'shop.enabled': <?php echo config('shop.enabled') === true ? 'true' : 'false'; ?>,
'shop.cart_count': <?php echo config('shop.enabled') === true ? Vektor\Shop\Utilities::cart()->product_count : 'null'; ?>,
'shop.filters.enabled': <?php echo config('shop.filters.enabled') === true ? 'true' : 'false'; ?>,
'shop.pagination.enabled': <?php echo config('shop.pagination.enabled') === true ? 'true' : 'false'; ?>,
'shop.pagination.per_pages': [<?php echo config('shop.pagination.per_pages'); ?>],
'shop.hide_pricing': <?php echo config('shop.hide_pricing') === true ? 'true' : 'false'; ?>,
'shop.use_user_addresses': <?php echo config('shop.use_user_addresses') === true ? 'true' : 'false'; ?>,
'shop.billing_required': <?php echo config('shop.billing_required') === true ? 'true' : 'false'; ?>,
'shop.shipping_required': <?php echo config('shop.shipping_required') === true ? 'true' : 'false'; ?>,
'shop.customer_unique': <?php echo config('shop.customer_unique') === true ? 'true' : 'false'; ?>,
'shop.email_domain_check.enabled': <?php echo config('shop.email_domain_check.enabled') === true ? 'true' : 'false'; ?>,
'shop.email_domain_check.list': '<?php echo config('shop.email_domain_check.list'); ?>',
'shop.agree_terms': <?php echo config('shop.agree_terms') === true ? 'true' : 'false'; ?>,
'shop.agree_marketing': <?php echo config('marketing.enabled') === true && config('shop.agree_marketing') === true ? 'true' : 'false'; ?>,
'shop.cart.index': '<?php echo route('shop.cart.index'); ?>',
'shop.checkout.index': '<?php echo route('shop.checkout.index'); ?>',
'shop.minimum_country_qty': <?php echo !empty(config('shop.minimum_country_qty')) === true ? 'true' : 'false'; ?>,
'payments.account.enabled': <?php echo config('account.enabled') === true ? 'true' : 'false'; ?>,
'payments.cash.enabled': <?php echo config('cash.enabled') === true ? 'true' : 'false'; ?>,
'payments.purchase_order.enabled': <?php echo config('purchase_order.enabled') === true ? 'true' : 'false'; ?>,
'payments.paypal.enabled': <?php echo config('paypal.enabled') === true ? 'true' : 'false'; ?>,
'payments.stripe.enabled': <?php echo config('stripe.enabled') === true ? 'true' : 'false'; ?>,
'payments.stripe.public_key': '<?php echo config('stripe.public_key'); ?>',
'payments.stripe.request.enabled': <?php echo config('stripe.request.enabled') === true ? 'true' : 'false'; ?>,
@yield('config')
};
const json = JSON.stringify(params);
window._configParams = Object.freeze(window.btoa(json));
</script>
@section('speculationrules')
<script type="speculationrules">
{
    "prerender": [{
        "source": "document",
        "where": {
            "and": [
                { "href_matches": "/*" },
                { "not": {
                    "href_matches": "/logout/*"
                }}
            ]
        },
        "eagerness": "moderate"
    }]
}
</script>
@show
@yield('extra')