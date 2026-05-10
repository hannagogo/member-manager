<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Organizations</title>

    <style>
        body {
            font-family: sans-serif;
            padding: 24px;
        }

        ul {
            line-height: 1.8;
        }

        .org-code {
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>

<h1>Organizations</h1>

@php
function renderOrganizationTree($organizations) {
    echo '<ul>';

    foreach ($organizations as $organization) {

        echo '<li>';

        echo e($organization->name);

        if ($organization->code) {
            echo ' <span class="org-code">(' . e($organization->code) . ')</span>';
        }

        if ($organization->children->count()) {
            renderOrganizationTree($organization->children);
        }

        echo '</li>';
    }

    echo '</ul>';
}
@endphp

{!! renderOrganizationTree($organizations) !!}

</body>
</html>
