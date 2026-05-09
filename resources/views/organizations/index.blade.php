@extends('layouts.admin')

@section('content')
<h2>Organizations</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Parent</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($organizations as $organization)
            <tr>
                <td>
                    <a href="/organizations/{{ $organization->id }}">
                        {{ $organization->name }}
                    </a>
                </td>
                <td>{{ $organization->type }}</td>
                <td>{{ $organization->parent?->name }}</td>
                <td>{{ $organization->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
