<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Razorpay Payment Update</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .card {
            border-radius: 12px;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">

                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Payment Status Update</h4>
                    </div>

                    <div class="card-body">

                        {{-- Success Message --}}
                        @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif

                        {{-- Error Message --}}
                        @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        <form method="POST" action="{{ url('razorpay/payment/update') }}">
                            @csrf

                            <!-- Password -->
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>

                                @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Order ID -->
                            <div class="mb-3">
                                <label class="form-label">Order ID</label>
                                <input type="text" name="order_id" value="{{ old('order_id') }}" class="form-control @error('order_id') is-invalid @enderror" required>

                                @error('order_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Status Dropdown -->
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="completed" {{ old('status')=='completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="failed" {{ old('status')=='failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="refunded" {{ old('status')=='refunded' ? 'selected' : '' }}>Refunded</option>
                                    <option value="processing" {{ old('status')=='processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="pending" {{ old('status')=='pending' ? 'selected' : '' }}>Pending</option>
                                </select>

                                @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    Update Payment
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>