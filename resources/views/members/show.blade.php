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
    <li>{{ $memberRole->role->display_name }} / {{ $memberRole->status }}</li>
@endforeach
</ul>



<h3>Effective Permissions</h3>

<style>
.permission-result-allow {
    color: #0a7a0a;
    font-weight: bold;
}

.permission-result-deny {
    color: #c62828;
    font-weight: bold;
}

.permission-trace {
    margin: 8px 0 16px;
    padding: 12px;
    border: 1px solid #ddd;
    background: #fafafa;
}

.permission-trace table {
    width: 100%;
    border-collapse: collapse;
}

.permission-trace th,
.permission-trace td {
    border: 1px solid #ddd;
    padding: 6px 8px;
    text-align: left;
    vertical-align: top;
}

.permission-summary {
    margin-bottom: 4px;
}
</style>

@foreach ($effectivePermissions as $permission => $trace)

<div class="permission-trace">

    <div class="permission-summary">
        <strong>{{ $permission }}</strong>
        —
        <span class="permission-result-{{ $trace['result'] }}">
            {{ strtoupper($trace['result']) }}
        </span>
    </div>

    <details>
        <summary>Trace Details</summary>

        <table>
            <thead>
                <tr>
                    <th>Result</th>
                    <th>Source Type</th>
                    <th>Source</th>
                    <th>Organization</th>
                    <th>Reason</th>
                </tr>
            </thead>

            <tbody>

            @foreach ($trace['traces'] as $item)

                <tr>
                    <td>{{ $item['result'] }}</td>
                    <td>{{ $item['source_type'] }}</td>
                    <td>{{ $item['source_name'] }}</td>
                    <td>{{ $item['organization'] }}</td>
                    <td>{{ $item['reason'] }}</td>
                </tr>

            @endforeach

            </tbody>
        </table>

    </details>

</div>

@endforeach


<p><a href="/members">Back to members</a></p>
@endsection
