<?php

namespace App\Http\Controllers;

use App\Models\AuthProfile;
use App\Models\H2hEndpoint;
use App\Models\H2hRequestTemplate;
use App\Models\H2hSystem;
use App\Models\H2hTestRun;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class H2hToolController extends Controller
{
    public function systemsPage()
    {
        return view('systems', [
            'systems' => H2hSystem::query()->orderBy('name')->get(),
            'editSystem' => null,
        ]);
    }

    public function editSystemPage(H2hSystem $system)
    {
        return view('systems', [
            'systems' => H2hSystem::query()->orderBy('name')->get(),
            'editSystem' => $system,
        ]);
    }

    public function authProfilesPage(Request $request)
    {
        $selectedSystemId = $request->integer('system_id');

        return view('auth-profiles', [
            'systems' => H2hSystem::query()->orderBy('name')->get(),
            'selectedSystemId' => $selectedSystemId,
            'editAuthProfile' => null,
            'authProfiles' => AuthProfile::query()
                ->with('system')
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->get(),
        ]);
    }

    public function editAuthProfilePage(AuthProfile $authProfile, Request $request)
    {
        $selectedSystemId = $request->integer('system_id') ?: $authProfile->h2h_system_id;

        return view('auth-profiles', [
            'systems' => H2hSystem::query()->orderBy('name')->get(),
            'selectedSystemId' => $selectedSystemId,
            'editAuthProfile' => $authProfile,
            'authProfiles' => AuthProfile::query()
                ->with('system')
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->get(),
        ]);
    }

    public function endpointsPage(Request $request)
    {
        $selectedSystemId = $request->integer('system_id');

        return view('endpoints', [
            'systems' => H2hSystem::query()->orderBy('name')->get(),
            'selectedSystemId' => $selectedSystemId,
            'editEndpoint' => null,
            'authProfiles' => AuthProfile::query()
                ->with('system')
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->get(),
            'endpoints' => H2hEndpoint::query()
                ->with(['authProfile', 'system'])
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->get(),
        ]);
    }

    public function editEndpointPage(H2hEndpoint $endpoint, Request $request)
    {
        $selectedSystemId = $request->integer('system_id') ?: $endpoint->h2h_system_id;

        return view('endpoints', [
            'systems' => H2hSystem::query()->orderBy('name')->get(),
            'selectedSystemId' => $selectedSystemId,
            'editEndpoint' => $endpoint,
            'authProfiles' => AuthProfile::query()
                ->with('system')
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->get(),
            'endpoints' => H2hEndpoint::query()
                ->with(['authProfile', 'system'])
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->get(),
        ]);
    }

    public function runTestsPage(Request $request)
    {
        $selectedSystemId = $request->integer('system_id');

        return view('run-tests', [
            'systems' => H2hSystem::query()->orderBy('name')->get(),
            'selectedSystemId' => $selectedSystemId,
            'requestTemplates' => H2hRequestTemplate::query()
                ->with('system')
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->get(),
            'endpoints' => H2hEndpoint::query()
                ->with(['authProfile', 'system'])
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->get(),
            'testRuns' => H2hTestRun::query()
                ->with(['endpoint', 'system'])
                ->when($selectedSystemId, fn ($query) => $query->where('h2h_system_id', $selectedSystemId))
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    public function storeSystem(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:h2h_systems,code'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        H2hSystem::query()->create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect('/systems')->with('status', 'System/project berhasil disimpan.');
    }

    public function updateSystem(Request $request, H2hSystem $system)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('h2h_systems', 'code')->ignore($system->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $system->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect('/systems')->with('status', 'System/project berhasil diupdate.');
    }

    public function storeAuthProfile(Request $request)
    {
        $validated = $request->validate([
            'h2h_system_id' => ['required', 'integer', 'exists:h2h_systems,id'],
            'name' => ['required', 'string', 'max:255'],
            'auth_type' => ['required', Rule::in(['none', 'bearer', 'basic', 'api_key_header', 'creatio'])],
            'token' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'creatio_login_path' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string'],
            'api_key_header' => ['nullable', 'string', 'max:255'],
            'extra_headers' => ['nullable', 'string'],
        ]);

        $extraHeaders = $this->decodeJsonArray($validated['extra_headers'] ?? null, 'extra_headers');

        AuthProfile::query()->create([
            'h2h_system_id' => $validated['h2h_system_id'],
            'name' => $validated['name'],
            'auth_type' => $validated['auth_type'],
            'token' => $validated['token'] ?? null,
            'username' => $validated['username'] ?? null,
            'password' => $validated['password'] ?? null,
            'creatio_login_path' => $validated['creatio_login_path'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
            'api_key_header' => $validated['api_key_header'] ?? null,
            'extra_headers' => $extraHeaders,
        ]);

        return redirect('/auth-profiles')->with('status', 'Auth profile berhasil disimpan.');
    }

    public function updateAuthProfile(Request $request, AuthProfile $authProfile)
    {
        $validated = $request->validate([
            'h2h_system_id' => ['required', 'integer', 'exists:h2h_systems,id'],
            'name' => ['required', 'string', 'max:255'],
            'auth_type' => ['required', Rule::in(['none', 'bearer', 'basic', 'api_key_header', 'creatio'])],
            'token' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'creatio_login_path' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string'],
            'api_key_header' => ['nullable', 'string', 'max:255'],
            'extra_headers' => ['nullable', 'string'],
        ]);

        $extraHeaders = $this->decodeJsonArray($validated['extra_headers'] ?? null, 'extra_headers');

        $authProfile->update([
            'h2h_system_id' => $validated['h2h_system_id'],
            'name' => $validated['name'],
            'auth_type' => $validated['auth_type'],
            'token' => $validated['token'] ?? null,
            'username' => $validated['username'] ?? null,
            'password' => $validated['password'] ?? null,
            'creatio_login_path' => $validated['creatio_login_path'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
            'api_key_header' => $validated['api_key_header'] ?? null,
            'extra_headers' => $extraHeaders,
        ]);

        return redirect('/auth-profiles')->with('status', 'Auth profile berhasil diupdate.');
    }

    public function storeEndpoint(Request $request)
    {
        $validated = $request->validate([
            'h2h_system_id' => ['required', 'integer', 'exists:h2h_systems,id'],
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url'],
            'path' => ['required', 'string', 'max:255'],
            'method' => ['required', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])],
            'timeout_seconds' => ['required', 'integer', 'min:1', 'max:120'],
            'auth_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('auth_profiles', 'id')->where(
                    fn ($query) => $query->where('h2h_system_id', $request->input('h2h_system_id'))
                ),
            ],
            'default_headers' => ['nullable', 'string'],
        ]);

        $defaultHeaders = $this->decodeJsonArray($validated['default_headers'] ?? null, 'default_headers');

        H2hEndpoint::query()->create([
            'h2h_system_id' => $validated['h2h_system_id'],
            'name' => $validated['name'],
            'base_url' => rtrim($validated['base_url'], '/'),
            'path' => '/' . ltrim($validated['path'], '/'),
            'method' => $validated['method'],
            'timeout_seconds' => $validated['timeout_seconds'],
            'auth_profile_id' => $validated['auth_profile_id'] ?? null,
            'default_headers' => $defaultHeaders,
        ]);

        return redirect('/endpoints')->with('status', 'Endpoint H2H berhasil disimpan.');
    }

    public function updateEndpoint(Request $request, H2hEndpoint $endpoint)
    {
        $validated = $request->validate([
            'h2h_system_id' => ['required', 'integer', 'exists:h2h_systems,id'],
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url'],
            'path' => ['required', 'string', 'max:255'],
            'method' => ['required', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])],
            'timeout_seconds' => ['required', 'integer', 'min:1', 'max:120'],
            'auth_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('auth_profiles', 'id')->where(
                    fn ($query) => $query->where('h2h_system_id', $request->input('h2h_system_id'))
                ),
            ],
            'default_headers' => ['nullable', 'string'],
        ]);

        $defaultHeaders = $this->decodeJsonArray($validated['default_headers'] ?? null, 'default_headers');

        $endpoint->update([
            'h2h_system_id' => $validated['h2h_system_id'],
            'name' => $validated['name'],
            'base_url' => rtrim($validated['base_url'], '/'),
            'path' => '/' . ltrim($validated['path'], '/'),
            'method' => $validated['method'],
            'timeout_seconds' => $validated['timeout_seconds'],
            'auth_profile_id' => $validated['auth_profile_id'] ?? null,
            'default_headers' => $defaultHeaders,
        ]);

        return redirect('/endpoints')->with('status', 'Endpoint H2H berhasil diupdate.');
    }

    public function run(Request $request)
    {
        $validated = $request->validate([
            'endpoint_id' => ['required', 'integer', 'exists:h2h_endpoints,id'],
            'request_template_id' => ['nullable', 'integer', 'exists:h2h_request_templates,id'],
            'request_headers' => ['nullable', 'string'],
            'request_body' => ['nullable', 'string'],
            'request_body_file' => ['nullable', 'file', 'mimes:json,txt', 'max:2048'],
            'save_as_template' => ['nullable', 'boolean'],
            'template_name' => ['nullable', 'string', 'max:255'],
            'template_description' => ['nullable', 'string', 'max:255'],
        ]);

        $endpoint = H2hEndpoint::query()->with('authProfile')->findOrFail($validated['endpoint_id']);
        $requestHeaders = $this->decodeJsonArray($validated['request_headers'] ?? null, 'request_headers');

        $selectedTemplate = null;
        if (! empty($validated['request_template_id'])) {
            $selectedTemplate = H2hRequestTemplate::query()
                ->where('h2h_system_id', $endpoint->h2h_system_id)
                ->find($validated['request_template_id']);

            if (! $selectedTemplate) {
                throw ValidationException::withMessages([
                    'request_template_id' => 'Template tidak ditemukan untuk system endpoint ini.',
                ]);
            }
        }

        $mergedHeaders = array_merge($endpoint->default_headers ?? [], $requestHeaders ?? []);
        $url = rtrim($endpoint->base_url, '/') . '/' . ltrim($endpoint->path, '/');
        $requestBody = $this->resolveRequestBodyFromInput($request, $validated, $selectedTemplate?->request_body);
        $requestBody = $requestBody === '' ? null : $requestBody;

        if (($validated['save_as_template'] ?? false) && $requestBody !== null) {
            if (empty($validated['template_name'])) {
                throw ValidationException::withMessages([
                    'template_name' => 'Nama template wajib diisi saat memilih simpan template.',
                ]);
            }

            H2hRequestTemplate::query()->create([
                'h2h_system_id' => $endpoint->h2h_system_id,
                'name' => $validated['template_name'],
                'description' => $validated['template_description'] ?? null,
                'request_body' => $requestBody,
            ]);
        }

        $start = microtime(true);
        $responseStatus = null;
        $responseHeaders = null;
        $responseBody = null;
        $errorMessage = null;

        try {
            $client = Http::timeout($endpoint->timeout_seconds)->acceptJson();
            $client = $this->applyAuth($client, $endpoint->authProfile, $endpoint);

            if (! empty($mergedHeaders)) {
                $client = $client->withHeaders($mergedHeaders);
            }

            $method = strtoupper($endpoint->method);
            if ($requestBody !== null && ! in_array($method, ['GET', 'DELETE'], true)) {
                $decodedBody = json_decode($requestBody, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedBody)) {
                    $response = $client->{$method}($url, $decodedBody);
                } else {
                    $response = $client->withBody($requestBody, $mergedHeaders['Content-Type'] ?? 'application/json')->send($method, $url);
                }
            } else {
                $response = $client->send($method, $url);
            }

            $responseStatus = $response->status();
            $responseHeaders = $response->headers();
            $responseBody = $response->body();
        } catch (\Throwable $exception) {
            $errorMessage = $exception->getMessage();
        }

        H2hTestRun::query()->create([
            'h2h_system_id' => $endpoint->h2h_system_id,
            'h2h_endpoint_id' => $endpoint->id,
            'request_url' => $url,
            'request_method' => strtoupper($endpoint->method),
            'request_headers' => $mergedHeaders,
            'request_body' => $requestBody,
            'response_status' => $responseStatus,
            'response_headers' => $responseHeaders,
            'response_body' => $responseBody,
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'error_message' => $errorMessage,
        ]);

        return redirect('/run-tests')->with('status', 'Test H2H selesai dijalankan.');
    }

    private function applyAuth(PendingRequest $client, ?AuthProfile $authProfile, H2hEndpoint $endpoint): PendingRequest
    {
        if ($authProfile === null) {
            return $client;
        }

        if (! empty($authProfile->extra_headers)) {
            $client = $client->withHeaders($authProfile->extra_headers);
        }

        if ($authProfile->auth_type === 'none') {
            return $client;
        }

        if ($authProfile->auth_type === 'bearer' && $authProfile->token) {
            return $client->withToken($authProfile->token);
        }

        if ($authProfile->auth_type === 'basic' && $authProfile->username && $authProfile->password) {
            return $client->withBasicAuth($authProfile->username, $authProfile->password);
        }

        if ($authProfile->auth_type === 'api_key_header' && $authProfile->api_key) {
            return $client->withHeader($authProfile->api_key_header ?: 'X-API-KEY', $authProfile->api_key);
        }

        if ($authProfile->auth_type === 'creatio') {
            if (! $authProfile->username || ! $authProfile->password) {
                throw new RuntimeException('Auth Creatio butuh username dan password.');
            }

            $loginPath = $authProfile->creatio_login_path ?: '/ServiceModel/AuthService.svc/Login';
            $loginUrl = rtrim($endpoint->base_url, '/') . '/' . ltrim($loginPath, '/');

            $loginResponse = Http::timeout($endpoint->timeout_seconds)
                ->acceptJson()
                ->withHeaders($authProfile->extra_headers ?? [])
                ->post($loginUrl, [
                    'UserName' => $authProfile->username,
                    'UserPassword' => $authProfile->password,
                ]);

            if (! $loginResponse->successful()) {
                throw new RuntimeException('Login Creatio gagal dengan status ' . $loginResponse->status());
            }

            $headers = $loginResponse->headers();
            $setCookies = $headers['Set-Cookie'] ?? $headers['set-cookie'] ?? [];
            $cookies = $this->parseCookieHeaders($setCookies);
            $domain = parse_url($endpoint->base_url, PHP_URL_HOST) ?: '';

            if (! empty($cookies) && $domain !== '') {
                $client = $client->withCookies($cookies, $domain);
            }

            $bpmcsrf = $headers['BPMCSRF'][0] ?? $headers['bpmcsrf'][0] ?? ($cookies['BPMCSRF'] ?? null);
            if ($bpmcsrf) {
                $client = $client->withHeader('BPMCSRF', $bpmcsrf);
            }

            return $client;
        }

        return $client;
    }

    private function parseCookieHeaders(array $setCookies): array
    {
        $cookies = [];

        foreach ($setCookies as $setCookie) {
            $cookiePart = explode(';', $setCookie)[0] ?? '';
            if (! str_contains($cookiePart, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $cookiePart, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name !== '' && $value !== '') {
                $cookies[$name] = $value;
            }
        }

        return $cookies;
    }

    private function decodeJsonArray(?string $rawValue, string $fieldName): ?array
    {
        if ($rawValue === null || trim($rawValue) === '') {
            return null;
        }

        $decoded = json_decode($rawValue, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw ValidationException::withMessages([
                $fieldName => "Field {$fieldName} harus berupa JSON object.",
            ]);
        }

        return $decoded;
    }

    private function resolveRequestBodyFromInput(Request $request, array $validated, ?string $templateBody): ?string
    {
        $uploadedBody = $this->readJsonBodyFromUploadedFile($request->file('request_body_file'));
        if ($uploadedBody !== null) {
            return $uploadedBody;
        }

        return $validated['request_body'] ?? $templateBody;
    }

    private function readJsonBodyFromUploadedFile(?UploadedFile $uploadedFile): ?string
    {
        if ($uploadedFile === null) {
            return null;
        }

        $rawBody = trim((string) $uploadedFile->get());
        if ($rawBody === '') {
            throw ValidationException::withMessages([
                'request_body_file' => 'File request body JSON tidak boleh kosong.',
            ]);
        }

        $decoded = json_decode($rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                'request_body_file' => 'Isi file harus JSON valid.',
            ]);
        }

        return json_encode($decoded, JSON_UNESCAPED_SLASHES);
    }
}
