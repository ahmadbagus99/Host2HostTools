<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth Profiles - H2H Testing Tool</title>
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
        $editExtraHeaders = old('extra_headers');
        if ($editExtraHeaders === null && isset($editAuthProfile) && $editAuthProfile?->extra_headers) {
            $editExtraHeaders = json_encode($editAuthProfile->extra_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    @endphp
    <h1>H2H Testing Tool</h1>
    <div class="nav">
        <a href="/systems">Systems</a>
        <a href="/auth-profiles" class="active">Auth Profiles</a>
        <a href="/endpoints">Endpoints</a>
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
        <h2>{{ $editAuthProfile ? 'Edit Auth Profile' : 'Tambah Auth Profile' }}</h2>
        <form method="POST" action="{{ $editAuthProfile ? '/auth-profiles/'.$editAuthProfile->id : '/auth-profiles' }}">
            @csrf
            @if ($editAuthProfile)
                @method('PUT')
            @endif
            <label>System / Project</label>
            <select name="h2h_system_id" required>
                <option value="">-- Pilih System --</option>
                @foreach ($systems as $system)
                    <option value="{{ $system->id }}" @selected((int) old('h2h_system_id', $editAuthProfile->h2h_system_id ?? 0) === $system->id)>{{ $system->name }} ({{ $system->code }})</option>
                @endforeach
            </select>

            <label>Nama Profile</label>
            <input name="name" placeholder="Contoh: Bearer UAT" value="{{ old('name', $editAuthProfile->name ?? '') }}">

            <label>Tipe Auth</label>
            <select name="auth_type">
                <option value="none" @selected(old('auth_type', $editAuthProfile->auth_type ?? '') === 'none')>No Auth</option>
                <option value="bearer" @selected(old('auth_type', $editAuthProfile->auth_type ?? '') === 'bearer')>Bearer Token</option>
                <option value="basic" @selected(old('auth_type', $editAuthProfile->auth_type ?? '') === 'basic')>Basic Auth</option>
                <option value="api_key_header" @selected(old('auth_type', $editAuthProfile->auth_type ?? '') === 'api_key_header')>API Key Header</option>
                <option value="creatio" @selected(old('auth_type', $editAuthProfile->auth_type ?? '') === 'creatio')>Creatio Login + BPMCSRF</option>
            </select>

            <label>Bearer Token</label>
            <input name="token" value="{{ old('token', $editAuthProfile->token ?? '') }}">

            <label>Basic Username</label>
            <input name="username" value="{{ old('username', $editAuthProfile->username ?? '') }}">

            <label>Basic Password</label>
            <input name="password" type="password" value="{{ old('password', $editAuthProfile->password ?? '') }}">

            <label>Creatio Login Path</label>
            <input name="creatio_login_path" placeholder="/ServiceModel/AuthService.svc/Login" value="{{ old('creatio_login_path', $editAuthProfile->creatio_login_path ?? '') }}">

            <label>API Key</label>
            <input name="api_key" value="{{ old('api_key', $editAuthProfile->api_key ?? '') }}">

            <label>Nama Header API Key</label>
            <input name="api_key_header" placeholder="X-API-KEY" value="{{ old('api_key_header', $editAuthProfile->api_key_header ?? '') }}">

            <label>Extra Headers (JSON object)</label>
            <textarea name="extra_headers" placeholder='{"X-CUSTOM":"value"}'>{{ $editExtraHeaders }}</textarea>

            <button type="submit">{{ $editAuthProfile ? 'Update Auth Profile' : 'Simpan Auth Profile' }}</button>
            @if ($editAuthProfile)
                <a href="/auth-profiles">Batal edit</a>
            @endif
        </form>
    </section>

    <section class="card">
        <h2>Daftar Auth Profile</h2>
        <form method="GET" action="/auth-profiles">
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
                    <th>Nama</th>
                    <th>Tipe Auth</th>
                    <th>Creatio Path</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($authProfiles as $profile)
                    <tr>
                        <td>{{ $profile->system?->code }}</td>
                        <td>{{ $profile->name }}</td>
                        <td>{{ $profile->auth_type }}</td>
                        <td>{{ $profile->creatio_login_path ?? '-' }}</td>
                        <td><a href="/auth-profiles/{{ $profile->id }}/edit?system_id={{ $selectedSystemId }}">Edit</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada auth profile.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</body>
</html>
