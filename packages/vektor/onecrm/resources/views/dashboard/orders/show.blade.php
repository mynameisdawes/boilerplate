@extends('layouts.dashboard')
@php
    $default_title = 'My Order';
@endphp
@if (isset($page))
    @section('title', $page->title ? $page->title : $default_title)
    @section('meta_title', !empty($page->meta_title) ? $page->meta_title : $page->title)
    @section('meta_description', $page->meta_description)
    @section('meta_image', $page->meta_image)
@else
    @section('title', $default_title)
@endif

@section('content.dashboard')
<c-onecrm_order id="{{ $id }}">
    <template v-slot:default="crmOrderScope">
        <div class="mb-8 -mt-6:3">
            <ul class="breadcrumbs">
                <li><a href="{{ route('dashboard.onecrm.orders.index') }}">Back to My orders</a></li>
            </ul>
        </div>
        <div class="border-box">
            <div class="relative" style="min-height: 200px;">
                <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: crmOrderScope.is_loading == true }">
                    <div class="spinner"></div>
                </div>
                <div v-if="crmOrderScope.data_fetched == true && crmOrderScope.order">
                    <div class="bg-secondary text-secondary_contrasting p-8 pr-28:3">
                        <h1>Order #@{{ crmOrderScope.order.number }}</h1>
                        <p v-if="crmOrderScope.order.name">@{{ crmOrderScope.order.name }}</p>
                    </div>
                    <div class="p-8">
                        <div class="absolute:3 top-0:3 right-0:3 pt-4:3 pr-4:3 mb-4:1t2e">
                            <span v-if="crmOrderScope.order.status" class="badge bg-primary:1t2e text-primary_contrasting:1t2e bg-secondary_contrasting:3 text-secondary:3">@{{ crmOrderScope.order.status }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4">
                            <div v-if="crmOrderScope.order.shipping_address">
                                <h3>Shipping Address</h3>
                                <p v-html="crmOrderScope.order.shipping_address"></p>
                            </div>
                            <div v-if="crmOrderScope.order.billing_address">
                                <h3>Billing Address</h3>
                                <p v-html="crmOrderScope.order.billing_address"></p>
                            </div>
                        </div>
                        <table class="table--card:1e table--spacious:3 table--edgeless:3 my-10">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Unit Price</th>
                                    <th>Qty</th>
                                    <th>Net Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="line in crmOrderScope.order.lines">
                                    <td data-header="Name">@{{ line.name }}</td>
                                    <td data-header="Unit Price">@{{ line.formatted.unit_price }}</td>
                                    <td data-header="Qty">@{{ line.quantity }}</td>
                                    <td data-header="Net Price">@{{ line.formatted.ext_price }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                    <td>Tax:</td>
                                    <td>@{{ crmOrderScope.order.formatted.total_tax }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3">&nbsp;</td>
                                    <td><strong>@{{ crmOrderScope.order.formatted.amount }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                        {{-- <p v-if="crmOrderScope.order.formatted.subtotal"><strong>Subtotal:</strong> @{{ crmOrderScope.order.formatted.subtotal }}</p> --}}
                        {{-- <p v-if="crmOrderScope.order.formatted.amount"><strong>Total:</strong> @{{ crmOrderScope.order.formatted.amount }}</p> --}}
                    </div>
                </div>
            </div>
        </div>
    </template>
</c-onecrm_order>
@endsection