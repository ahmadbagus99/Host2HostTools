<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Systems - H2H Testing Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f5f7fa; color: #1f2937; }
        h1, h2 { margin-bottom: 10px; }
        .card { background: #fff; border-radius: 10px; padding: 16px; margin-bottom: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        label { display: block; font-weight: 600; margin: 10px 0 4px; }
        input, button { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
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
    <h1>H2H Testing Tool</h1>
    <div class="nav">
        <a href="/systems" class="active">Systems</a>
        <a href="/auth-profiles">Auth Profiles</a>
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
        <h2>Tambah System / Project</h2>
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
        <h2>Daftar System / Project</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kode</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($systems as $system)
                    <tr>
                        <td>{{ $system->name }}</td>
                        <td>{{ $system->code }}</td>
                        <td>{{ $system->description ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada system.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</body>
</html>
