<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Tests - H2H Testing Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f5f7fa; color: #1f2937; }
        h1, h2 { margin-bottom: 10px; }
        .card { background: #fff; border-radius: 10px; padding: 16px; margin-bottom: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        label { display: block; font-weight: 600; margin: 10px 0 4px; }
        select, textarea, button, input[type="text"] { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
        textarea { min-height: 280px; font-family: monospace; }
        button { background: #2563eb; color: #fff; border: none; cursor: pointer; margin-top: 12px; }
        .status { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        .error { background: #fef2f2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        pre { white-space: pre-wrap; word-break: break-word; margin: 0; font-size: 12px; background: #0b1020; color: #d1e7ff; padding: 10px; border-radius: 8px; }
        .nav { display: flex; gap: 10px; margin-bottom: 16px; }
        .nav a { background: #e5e7eb; color: #111827; text-decoration: none; padding: 8px 12px; border-radius: 8px; }
        .nav a.active { background: #2563eb; color: #fff; }
        .button-secondary { background: #4b5563; margin-top: 8px; }
        .btn-show { background: #111827; color: #fff; border: none; border-radius: 6px; padding: 6px 10px; cursor: pointer; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); align-items: center; justify-content: center; z-index: 1000; }
        .modal-box { background: #fff; width: min(920px, 92vw); max-height: 85vh; overflow: auto; border-radius: 12px; padding: 16px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .modal-close { background: #dc2626; color: #fff; border: none; border-radius: 8px; padding: 8px 12px; cursor: pointer; }
        .checkbox-row { margin-top: 12px; display: flex; align-items: center; gap: 8px; }
        .checkbox-row input { width: auto; }
        .template-box { margin-top: 10px; display: none; }
        .json-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px; }
        @media (max-width: 960px) {
            .json-grid { grid-template-columns: 1fr; }
            textarea { min-height: 200px; }
        }
    </style>
</head>
<body>
    @php
        $formatAsPrettyJson = function (?string $raw) {
            if ($raw === null || trim($raw) === '') {
                return '-';
            }

            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            return $raw;
        };
        $templateJsonMap = [];
        foreach ($requestTemplates as $template) {
            $templateJsonMap[$template->id] = $formatAsPrettyJson($template->request_body);
        }
    @endphp
    <h1>H2H Testing Tool</h1>
    <div class="nav">
        <a href="/systems">Systems</a>
        <a href="/auth-profiles">Auth Profiles</a>
        <a href="/endpoints">Endpoints</a>
        <a href="/run-tests" class="active">Run Tests</a>
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
        <h2>Run Test H2H</h2>
        <form method="GET" action="/run-tests">
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

            <label>Pilih Request Body Template (opsional)</label>
            <select id="request_template_id" name="request_template_id" onchange="loadTemplateBody()">
                <option value="">-- Tanpa Template --</option>
                @foreach ($requestTemplates as $template)
                    <option value="{{ $template->id }}">[{{ $template->system?->code }}] {{ $template->name }}</option>
                @endforeach
            </select>

            <div class="json-grid">
                <div>
                    <label>Override Headers (JSON object)</label>
                    <textarea id="request_headers" name="request_headers" placeholder='{"X-Request-ID":"abc-123"}'></textarea>
                    <button type="button" class="button-secondary" onclick="formatJsonField('request_headers')">Rapikan JSON Headers</button>
                </div>

                <div>
                    <label>Request Body</label>
                    <textarea id="request_body" name="request_body" placeholder='{"claim_no":"CLM-001"}'></textarea>
                    <button type="button" class="button-secondary" onclick="formatJsonField('request_body')">Rapikan JSON Body</button>
                </div>
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="save_as_template" name="save_as_template" value="1" onchange="toggleTemplateSaveFields()">
                <label for="save_as_template">Simpan request body ini sebagai template</label>
            </div>
            <div id="template-save-fields" class="template-box">
                <label>Nama Template</label>
                <input type="text" name="template_name" placeholder="Contoh: Pengajuan Klaim Standar">

                <label>Deskripsi Template</label>
                <input type="text" name="template_description" placeholder="Opsional">
            </div>

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
                    <th>Response / Error</th>
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
                        <td>
                            <button
                                type="button"
                                class="btn-show"
                                onclick="showBodyModal('Request Body', @js($formatAsPrettyJson($run->request_body)))"
                            >
                                Show Request
                            </button>
                        </td>
                        <td>
                            @if ($run->error_message)
                                <button
                                    type="button"
                                    class="btn-show"
                                    onclick="showBodyModal('Error Message', @js($run->error_message))"
                                >
                                    Show Error
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="btn-show"
                                    onclick="showBodyModal('Response Body', @js($formatAsPrettyJson($run->response_body)))"
                                >
                                    Show Response
                                </button>
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

    <div id="body-modal" class="modal-backdrop" onclick="closeBodyModal(event)">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="body-modal-title">Detail</h3>
                <button type="button" class="modal-close" onclick="hideBodyModal()">Close</button>
            </div>
            <pre id="body-modal-content">-</pre>
        </div>
    </div>

    <script>
        const templateBodies = @json($templateJsonMap);

        function formatJsonField(elementId) {
            const input = document.getElementById(elementId);
            if (!input || !input.value.trim()) {
                return;
            }

            try {
                const parsed = JSON.parse(input.value);
                input.value = JSON.stringify(parsed, null, 2);
            } catch (error) {
                alert('Format JSON tidak valid.');
            }
        }

        function loadTemplateBody() {
            const templateId = document.getElementById('request_template_id').value;
            if (!templateId || !templateBodies[templateId]) {
                return;
            }

            document.getElementById('request_body').value = templateBodies[templateId];
        }

        function toggleTemplateSaveFields() {
            const checked = document.getElementById('save_as_template').checked;
            document.getElementById('template-save-fields').style.display = checked ? 'block' : 'none';
        }

        function showBodyModal(title, content) {
            document.getElementById('body-modal-title').textContent = title;
            document.getElementById('body-modal-content').textContent = content || '-';
            document.getElementById('body-modal').style.display = 'flex';
        }

        function hideBodyModal() {
            document.getElementById('body-modal').style.display = 'none';
        }

        function closeBodyModal(event) {
            if (event.target.id === 'body-modal') {
                hideBodyModal();
            }
        }
    </script>
</body>
</html>
