<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Console</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a2e; color: #fff; min-height: 100vh; padding: 20px; }
        .card { background: #16213e; border: 1px solid #0f3460; color: #fff; border-radius: 15px; }
        .form-control { background: #0f3460; border: 1px solid #533483; color: #fff; }
        .form-control:focus { background: #1a1a2e; color: #fff; border-color: #e94560; box-shadow: none; }
        .terminal { background: #000; color: #00ff00; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; max-height: 500px; overflow-y: auto; white-space: pre-wrap; }
        .btn-primary { background: #e94560; border: none; }
        .btn-info { background: #533483; border: none; color: #fff; }
        .btn-danger { background: #950101; border: none; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card shadow-lg">
            <div class="card-header border-0 d-flex justify-content-between align-items-center bg-transparent pt-4 px-4">
                <h2 class="mb-0">Artisan Console</h2>
                <div>
                    <a href="{{ route('admin.artisan.logs.list', ['code' => request('code')]) }}" class="btn btn-info me-2">Logs Manager</a>
                    <a href="{{ route('admin.artisan.request') }}" class="btn btn-outline-light">New Code</a>
                </div>
            </div>
            <div class="card-body p-4">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('admin.artisan.execute') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Access Code</label>
                            <input type="text" name="code" class="form-control" placeholder="6-digit code" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small text-muted">Command (e.g. migrate --force)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-0 text-white">php artisan</span>
                                <input type="text" name="command" class="form-control" placeholder="command" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Run</button>
                        </div>
                    </div>
                </form>

                @if(isset($output))
                    <div class="mt-4">
                        <h5 class="text-muted small mb-2">Execution Output (Exit Code: {{ $exitCode }})</h5>
                        <div class="terminal">{{ $output }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
