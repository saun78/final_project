<<<<<<< HEAD
=======
<!DOCTYPE html>
<html lang="en">
<head>
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>

  <style>
    body {
      background: linear-gradient(to right, #fef9f1, #d3ede6);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .card {
      border: none;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      background-color: #ffffff;
    }

    .card-header {
      background-color: #94cde1;
      color: #fff;
      text-align: center;
      padding: 1.5rem 1rem;
    }

    .form-control {
      border-radius: 10px;
    }

    .btn-primary {
      background-color: #558baf;
      border: none;
      border-radius: 10px;
      padding: 0.6rem;
      font-weight: 600;
    }

    .btn-primary:hover {
      background-color: #50799b;
    }

    .countdown-text {
      font-size: 0.9rem;
      margin-top: 0.3rem;
    }

    .fade-out {
      transition: opacity 1s ease;
      opacity: 0;
    }
  </style>
<<<<<<< HEAD
  <body>
=======
</head>
<body>
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42

  {{-- ✅ 成功弹窗 --}}
  @if (session('success'))
  <div id="success-alert"
       class="alert alert-success border-0 shadow-sm position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-3 text-center"
       style="z-index: 1050; min-width: 300px; max-width: 90%; border-radius: 8px; background-color: #73aa75; color: #fff;">
     {{ session('success') }}
    <div class="countdown-text small mt-2 text-light">
      Redirecting in <span id="countdown">3</span> seconds...
      <button class="btn btn-sm btn-outline-light ms-2 py-0 px-2" onclick="window.location.href='{{ route('dashboard') }}'">Go Now</button>
    </div>
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  <script>
    let seconds = 3;
    const countdownEl = document.getElementById('countdown');
    const successAlert = document.getElementById('success-alert');
    const interval = setInterval(() => {
      seconds--;
      countdownEl.innerText = seconds;
      if (seconds <= 0) {
        clearInterval(interval);
        successAlert.classList.add('fade-out');
        setTimeout(() => window.location.href = "{{ route('dashboard') }}", 1000);
      }
    }, 1000);
  </script>
  @endif

  {{-- ❌ 错误弹窗 --}}
  @if ($errors->any())
  <div id="error-alert"
       class="alert alert-danger border-0 shadow-sm position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-3 text-center"
       style="z-index: 1050; min-width: 300px; max-width: 90%; border-radius: 8px; background-color: #c75555; color: #fff;">
    ❌ Registration failed:
    <ul class="mt-2 text-start small mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  <script>
    const errorEl = document.getElementById('error-alert');
    setTimeout(() => {
      if (errorEl) {
        errorEl.classList.add('fade-out');
        setTimeout(() => errorEl.remove(), 1000);
      }
    }, 5000);
  </script>
  @endif

  {{-- 📄 表单 --}}
  <div class="col-md-5 col-lg-4">
    <div class="card">
      <div class="card-header">
        <h3>Register</h3>
      </div>
      <div class="card-body px-4 py-4">
        <form method="POST" action="{{ route('register') }}">
          @csrf

          {{-- Username --}}
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror"
                   id="username" name="username" value="{{ old('username') }}" required>
            @error('username')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Password --}}
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" class="form-control @error('password') is-invalid @enderror"
                     id="password" name="password" required>
              <span class="input-group-text" onclick="togglePassword('password')">
                <i class="bi bi-eye" id="eye-password"></i>
              </span>
            </div>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- Confirm Password --}}
          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-group">
              <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                     id="password_confirmation" name="password_confirmation" required>
              <span class="input-group-text" onclick="togglePassword('password_confirmation')">
                <i class="bi bi-eye" id="eye-password_confirmation"></i>
              </span>
<<<<<<< HEAD
</div>
=======
            </div>
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
            @error('password_confirmation')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Register</button>
          </div>
        </form>

        <div class="text-center mt-3">
          <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
<<<<<<< HEAD
</div>
      </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
=======
        </div>
      </div>
    </div>
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      const icon = document.getElementById('eye-' + id);
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    }
  </script>
</body>
<<<<<<< HEAD
</html> 
</html>
=======
</html>
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
