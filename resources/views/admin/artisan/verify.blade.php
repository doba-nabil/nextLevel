@extends('website.layout.master')
@section('title', 'Verify Artisan Access')
@section('website-main')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Verify Access</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <p>Please enter the 6-digit code sent to your email.</p>
                    <form action="{{ route('admin.artisan.verify.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Access Code</label>
                            <input type="text" name="code" class="form-control" required autofocus placeholder="123456">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify Code</button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('admin.artisan.request') }}" class="text-muted">Resend Code</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
