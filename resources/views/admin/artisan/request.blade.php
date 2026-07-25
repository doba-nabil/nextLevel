<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Access Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a2e; color: #fff; height: 100vh; display: flex; align-items: center; }
        .card { background: #16213e; border: 1px solid #0f3460; color: #fff; }
        .form-control { background: #0f3460; border: 1px solid #533483; color: #fff; }
        .form-control:focus { background: #1a1a2e; color: #fff; border-color: #e94560; box-shadow: none; }
        .btn-primary { background: #e94560; border: none; }
        .btn-primary:hover { background: #c62a48; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg p-4">
                    <h3 class="text-center mb-4">Artisan Access</h3>
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <p class="text-muted text-center small">Enter your authorized email to receive a temporary 6-digit access code.</p>
                    <form action="{{ route('admin.artisan.send-code') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control" required placeholder="Authorized Email">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Get Access Code</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
