<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H2H Testing Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f5f7fa; color: #1f2937; }
        h1, h2 { margin-bottom: 10px; }
        .card { background: #fff; border-radius: 10px; padding: 16px; margin-bottom: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; }
        label { display: block; font-weight: 600; margin: 10px 0 4px; }
        input, select, textarea, button { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
        textarea { min-height: 100px; font-family: monospace; }
        button { background: #2563eb; color: #fff; border: none; cursor: pointer; margin-top: 12px; }
        button:hover { background: #1d4ed8; }
        .status { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        .error { background: #fef2f2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        pre { white-space: pre-wrap; word-break: break-word; margin: 0; font-size: 12px; }
    </style>
</head>
<body>
    <h1>H2H Testing Tool (Laravel + PHP 8.3)</h1>

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

    <div class="grid">
        <section class="card">
            <h2>0) Setup System / Project</h2>
            <form method="POST" action="/systems">
                @csrf
                <label>Nama System</label>
                <input name="name" placeholder="Contoh: Jamkrida">

                <label>Kode Unik</label>
                <input name="code" placeholder="JAMKRIDA">

                <label>Deskripsi</label>
                <input name="description" placeholder="Opsional">

                <button type="submit">Simpan System</button>
            </form>
        </section>

        <section class="card">
            <h2>1) Setup Auth Integrasi</h2>
            <form method="POST" action="/auth-profiles">
                @csrf
                <label>System / Project</label>
                <select name="h2h_system_id" required>
                    <option value="">-- Pilih System --</option>
                    @foreach ($systems as $system)
                        <option value="{{ $system->id }}">{{ $system->name }} ({{ $system->code }})</option>
                    @endforeach
                </select>

                <label>Nama Profile</label>
                <input name="name" placeholder="Contoh: Mitra A - Bearer">

                <label>Tipe Auth</label>
                <select name="auth_type">
                    <option value="none">No Auth</option>
                    <option value="bearer">Bearer Token</option>
                    <option value="basic">Basic Auth</option>
                    <option value="api_key_header">API Key Header</option>
                </select>

                <label>Bearer Token</label>
                <input name="token" placeholder="token...">

                <label>Basic Username</label>
                <input name="username">

                <label>Basic Password</label>
                <input name="password" type="password">

                <label>API Key</label>
                <input name="api_key">

                <label>Nama Header API Key</label>
                <input name="api_key_header" placeholder="X-API-KEY">

                <label>Extra Headers (JSON object)</label>
                <textarea name="extra_headers" placeholder='{"X-CUSTOM":"value"}'></textarea>

                <button type="submit">Simpan Auth Profile</button>
            </form>
        </section>

        <section class="card">
            <h2>2) Setup Endpoint H2H</h2>
            <form method="POST" action="/endpoints">
                @csrf
                <label>System / Project</label>
                <select name="h2h_system_id" required>
                    <option value="">-- Pilih System --</option>
                    @foreach ($systems as $system)
                        <option value="{{ $system->id }}">{{ $system->name }} ({{ $system->code }})</option>
                    @endforeach
                </select>

                <label>Nama Endpoint</label>
                <input name="name" placeholder="Contoh: Pengajuan Klaim">

                <label>Base URL</label>
                <input name="base_url" placeholder="https://api-mitra.com">

                <label>Path</label>
                <input name="path" value="/v1/claim">

                <label>Method</label>
                <select name="method">
                    <option>POST</option>
                    <option>GET</option>
                    <option>PUT</option>
                    <option>PATCH</option>
                    <option>DELETE</option>
                </select>

                <label>Timeout (detik)</label>
                <input name="timeout_seconds" type="number" min="1" max="600" value="30">

                <label>Auth Profile</label>
                <select name="auth_profile_id">
                    <option value="">-- Tanpa Auth Profile --</option>
                    @foreach ($authProfiles as $profile)
                        <option value="{{ $profile->id }}">{{ $profile->name }} - {{ $profile->system?->code }} ({{ $profile->auth_type }})</option>
                    @endforeach
                </select>

                <label>Default Headers (JSON object)</label>
                <textarea name="default_headers" placeholder='{"Content-Type":"application/json"}'></textarea>

                <button type="submit">Simpan Endpoint</button>
            </form>
        </section>
    </div>

    <section class="card">
        <h2>3) Run Test H2H</h2>
        <form method="GET" action="/">
            <label>Filter System / Project</label>
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
        <form method="POST" action="/run-test">
            @csrf
            <label>Pilih Endpoint</label>
            <select name="endpoint_id">
                @foreach ($endpoints as $endpoint)
                    <option value="{{ $endpoint->id }}">
                        [{{ $endpoint->system?->code }}] {{ $endpoint->name }} - {{ $endpoint->method }} {{ $endpoint->base_url }}{{ $endpoint->path }}
                    </option>
                @endforeach
            </select>

            <label>Override Headers (JSON object)</label>
            <textarea name="request_headers" placeholder='{"X-Request-ID":"abc-123"}'></textarea>

            <label>Request Body</label>
            <textarea name="request_body" placeholder='{"claim_no":"CLM-001"}'></textarea>

            <button type="submit">Jalankan Test & Simpan Log</button>
        </form>
    </section>

    <section class="card">
        <h2>Log Test Terakhir</h2>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>System</th>
                    <th>Endpoint</th>
                    <th>Status</th>
                    <th>Durasi</th>
                    <th>Request Body</th>
                    <th>Response Body / Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($testRuns as $run)
                    <tr>
                        <td>{{ $run->created_at }}</td>
                        <td>{{ $run->system?->code ?? '-' }}</td>
                        <td>{{ $run->endpoint?->name }}<br>{{ $run->request_method }} {{ $run->request_url }}</td>
                        <td>{{ $run->response_status ?? '-' }}</td>
                        <td>{{ $run->duration_ms }} ms</td>
                        <td><pre>{{ $run->request_body }}</pre></td>
                        <td>
                            @if ($run->error_message)
                                <pre>{{ $run->error_message }}</pre>
                            @else
                                <pre>{{ $run->response_body }}</pre>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada test run.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</body>
</html>
