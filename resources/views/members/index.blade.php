@extends('layouts.admin')

@section('content')
<h2>Members</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Organizations</th>
            <th>Roles</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($members as $member)
            <tr>
                <td>
                    <a href="/members/{{ $member->id }}">
                        {{ $member->display_name }}
                    </a>
                </td>
                <td>{{ $member->status }}</td>
                <td>
                    @foreach ($member->memberships as $membership)
                        <div>{{ $membership->organization->name }}</div>
                    @endforeach
                </td>
                <td>
                    @foreach ($member->roles as $memberRole)
                        <div>{{ $memberRole->role->display_name }}</div>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
