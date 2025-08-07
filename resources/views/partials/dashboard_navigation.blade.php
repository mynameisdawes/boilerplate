@if (config('onecrm.enabled'))
    <li><a href="{{ route('dashboard.onecrm.index') }}">My account</a></li>
    <li><a href="{{ route('dashboard.onecrm.orders.index') }}">My orders</a></li>
@endif
<li><a href="{{ route('logout') }}">Log out</a></li>