<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Endpoints - H2H Testing Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f5f7fa; color: #1f2937; }
        h1, h2 { margin-bottom: 10px; }
        .card { background: #fff; border-radius: 10px; padding: 16px; margin-bottom: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        label { display: block; font-weight: 600; margin: 10px 0 4px; }
        input, select, textarea, button { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
        textarea { min-height: 80px; font-family: monospace; }
        button { background: #2563eb; color: #fff; border: none; cursor: pointer; margin-top: 12px; }
        .status { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        .error { background: #fef2f2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .nav { display: flex; gap: 10px; margin-bottom: 16px; }
        .nav a { background: #e5e7eb; color: #111827; text-decoration: none; padding: 8px 12px; border-radius: 8px; }
        .nav a.active { background: #2563eb; color: #fff; }
    </style>
</head>
<body>
    @php
        $editDefaultHeaders = old('default_headers');
        if ($editDefaultHeaders === null && isset($editEndpoint) && $editEndpoint?->default_headers) {
            $editDefaultHeaders = json_encode($editEndpoint->default_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    @endphp
    <h1>H2H Testing Tool</h1>
    <div class="nav">
        <a href="/systems">Systems</a>
        <a href="/auth-profiles">Auth Profiles</a>
        <a href="/endpoints" class="active">Endpoints</a>
        <a href="/run-tests">Run Tests</a>
    </div>

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="error">
            <strong>Validasi gagal:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card">
        <h2>{{ $editEndpoint ? 'Edit Endpoint H2H' : 'Tambah Endpoint H2H' }}</h2>
        <form method="POST" action="{{ $editEndpoint ? '/endpoints/'.$editEndpoint->id : '/endpoints' }}">
            @csrf
            @if ($editEndpoint)
                @method('PUT')
            @endif
            <label>System / Project</label>
            <select name="h2h_system_id" required>
                <option value="">-- Pilih System --</option>
                @foreach ($systems as $system)
                    <option value="{{ $system->id }}" @selected((int) old('h2h_system_id', $editEndpoint->h2h_system_id ?? 0) === $system->id)>{{ $system->name }} ({{ $system->code }})</option>
                @endforeach
            </select>

            <label>Nama Endpoint</label>
            <input name="name" placeholder="Contoh: Pengajuan Klaim" value="{{ old('name', $editEndpoint->name ?? '') }}">

            <label>Base URL</label>
            <input name="base_url" placeholder="https://api-mitra.com" value="{{ old('base_url', $editEndpoint->base_url ?? '') }}">

            <label>Path</label>
            <input name="path" value="{{ old('path', $editEndpoint->path ?? '/v1/claim') }}">

            <label>Method</label>
            <select name="method">
                <option @selected(old('method', $editEndpoint->method ?? '') === 'POST')>POST</option>
                <option @selected(old('method', $editEndpoint->method ?? '') === 'GET')>GET</option>
                <option @selected(old('method', $editEndpoint->method ?? '') === 'PUT')>PUT</option>
                <option @selected(old('method', $editEndpoint->method ?? '') === 'PATCH')>PATCH</option>
                <option @selected(old('method', $editEndpoint->method ?? '') === 'DELETE')>DELETE</option>
            </select>

            <label>Timeout (detik)</label>
            <input name="timeout_seconds" type="number" value="{{ old('timeout_seconds', $editEndpoint->timeout_seconds ?? 30) }}">

            <label>Auth Profile (opsional)</label>
            <select name="auth_profile_id">
                <option value="">-- Tanpa Auth Profile --</option>
                @foreach ($authProfiles as $profile)
                    <option value="{{ $profile->id }}" @selected((int) old('auth_profile_id', $editEndpoint->auth_profile_id ?? 0) === $profile->id)>{{ $profile->name }} - {{ $profile->system?->code }}</option>
                @endforeach
            </select>

            <label>Default Headers (JSON object)</label>
            <textarea name="default_headers" placeholder='{"Content-Type":"application/json"}'>{{ $editDefaultHeaders }}</textarea>

            <button type="submit">{{ $editEndpoint ? 'Update Endpoint' : 'Simpan Endpoint' }}</button>
            @if ($editEndpoint)
                <a href="/endpoints">Batal edit</a>
            @endif
        </form>
    </section>

    <section class="card">
        <h2>Daftar Endpoint</h2>
        <form method="GET" action="/endpoints">
            <label>Filter System</label>
            <select name="system_id" onchange="this.form.submit()">
                <option value="">-- Semua System --</option>
                @foreach ($systems as $system)
                    <option value="{{ $system->id }}" @selected((int) $selectedSystemId === $system->id)>
                        {{ $system->name }} ({{ $system->code }})
                    </option>
                @endforeach
            </select>
        </form>
        <br>
        <table>
            <thead>
                <tr>
                    <th>System</th>
                    <th>Endpoint</th>
                    <th>Method</th>
                    <th>Auth</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($endpoints as $endpoint)
                    <tr>
                        <td>{{ $endpoint->system?->code }}</td>
                        <td>{{ $endpoint->name }}<br>{{ $endpoint->base_url }}{{ $endpoint->path }}</td>
                        <td>{{ $endpoint->method }}</td>
                        <td>{{ $endpoint->authProfile?->name ?? '-' }}</td>
                        <td><a href="/endpoints/{{ $endpoint->id }}/edit?system_id={{ $selectedSystemId }}">Edit</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada endpoint.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</body>
</html>
