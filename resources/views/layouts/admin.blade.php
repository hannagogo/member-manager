<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Member Manager' }}</title>
</head>
<body>
    <header>
        <h1>Member Manager</h1>
        <nav>
            <a href="/members">Members</a> |
            <a href="/organizations">Organizations</a>
        </nav>
        <hr>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
