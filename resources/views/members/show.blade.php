@extends('layouts.admin')

@section('content')
<h2>{{ $member->display_name }}</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Status</th>
        <td>{{ $member->status }}</td>
    </tr>
    <tr>
        <th>Email</th>
        <td>{{ $member->email }}</td>
    </tr>
    <tr>
        <th>Phone</th>
        <td>{{ $member->phone }}</td>
    </tr>
</table>

<h3>Accounts</h3>
<ul>
@foreach ($member->accounts as $account)
    <li>{{ $account->provider }}: {{ $account->account_identifier }}</li>
@endforeach
</ul>

<h3>Organizations</h3>
<ul>
@foreach ($member->memberships as $membership)
    <li>{{ $membership->organization->name }} / {{ $membership->status }}</li>
@endforeach
</ul>

<h3>Roles</h3>
<ul>
@foreach ($member->roles as $memberRole)
    <li>{{ $memberRole->role->name }} / {{ $memberRole->status }}</li>
@endforeach
</ul>

<p><a href="/members">Back to members</a></p>
@endsection
