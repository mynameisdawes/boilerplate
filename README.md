## Boilerplate

vektor/cms
Run this to install Nova's deps
php artisan nova:install

Run this to copy the page, navigation and navigation item resources into the App namespace
php artisan vendor:publish --provider="Vektor\\ApiKeys\\ApiKeysServiceProvider"
php artisan vendor:publish --provider="Vektor\\CMS\\CMSServiceProvider"
php artisan vendor:publish --provider="Vektor\\Pages\\PagesServiceProvider"
php artisan vendor:publish --provider="Vektor\\Blog\\BlogServiceProvider"
php artisan vendor:publish --provider="Vektor\\Events\\EventsServiceProvider"
php artisan vendor:publish --provider="Vektor\\Shop\\ShopServiceProvider"

Run this command to seed the header menu and subfooter menu with the items that reflect the pages above
php artisan db:seed --class="Vektor\\CMS\\Database\\Seeders\\NavigationSeeder"

Run this command to seed the terms and policy pages
php artisan db:seed --class="Vektor\\Pages\\Database\\Seeders\\PageSeeder"

To use dynamic urls, use the page Blade directive to output the url as follows:
@page(2)

If using dynamic urls within Markdown content, use the directive in the following format:
[URL Text]({page:2})

vektor/shop
Run this command to seed some default shipping methods for the shop to use
php artisan db:seed --class="Vektor\\Shop\\Database\\Seeders\\ShippingSeeder"

These permissions can be added to the user configuration to modify the shop package behaviour:
    - can_purchase_services
    - can_customise_prices
    - can_create_quotes