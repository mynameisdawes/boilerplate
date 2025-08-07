@extends('layouts.dashboard')
@php
    $default_title = 'My Orders';
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
<c-onecrm_orders>
    <template v-slot:default="crmOrderScope">
        <div class="border-box bg-background p-8">
            <h1 class="text-gradient">My orders</h1>
            <hr style="margin-top: -1px;" v-if="crmOrderScope.is_loading == true">
            <div class="relative" style="min-height: 200px;">
                <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: crmOrderScope.is_loading == true }">
                    <div class="spinner"></div>
                </div>
                <table class="table--card:1t2e table--spacious:3 table--edgeless:3 mt-6:3" v-if="crmOrderScope.data_fetched && crmOrderScope.orders.length > 0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Date Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in crmOrderScope.orders">
                            <td data-header="Name"><a :href="'{{ url('dashboard/orders') }}/' + order.id">@{{ order.number + ' - ' + order.name }}</a></td>
                            <td data-header="Status">
                                <span class="badge bg-primary text-primary_contrasting">@{{ order.so_stage }}</span>
                            </td>
                            <td data-header="Amount">@{{ order.formatted.amount }}</td>
                            <td data-header="Date Created">@{{ order.date_entered }}</td>
                        </tr>
                    </tbody>
                </table>
                <c-message v-if="crmOrderScope.data_fetched && crmOrderScope.orders.length == 0" content="There are no orders yet" :trigger="true"></c-message>
            </div>
        </div>
    </template>
</c-onecrm_orders>
@endsection