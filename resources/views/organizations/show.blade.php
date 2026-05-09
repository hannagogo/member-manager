@extends('layouts.admin')

@section('content')
<h2>{{ $organization->name }}</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Type</th>
        <td>{{ $organization->type }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>{{ $organization->status }}</td>
    </tr>
    <tr>
        <th>Parent</th>
        <td>{{ $organization->parent?->name }}</td>
    </tr>
</table>

<h3>Members</h3>
<ul>
@foreach ($organization->memberships as $membership)
    <li>{{ $membership->member->display_name }} / {{ $membership->status }}</li>
@endforeach
</ul>

<p><a href="/organizations">Back to organizations</a></p>
@endsection
