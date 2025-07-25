<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/> {{-- 👈 加了 icons --}}
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

    .fade-out {
      opacity: 1;
      transition: opacity 1s ease-out;
    }

    .fade-out.hide {
      opacity: 0;
    }
  </style>
</head>
<body>

<<<<<<< HEAD
  {{-- ❌ 登录失败弹窗 --}}
  @if ($errors->any())
  <div id="error-alert"
       class="alert alert-danger border-0 shadow-sm position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-3 text-center fade-out d-flex align-items-center"
       style="z-index: 1050; min-width: 300px; max-width: 90%; border-radius: 8px; background-color: #f8d7da; color: #000;">
    <i class="bi bi-x-circle-fill me-2" style="font-size: 1.5rem; color: #dc3545;"></i>
    <div class="flex-grow-1 text-start">
      <strong>Login failed:</strong>
      <ul class="mt-2 text-start small mb-0">
        @if($errors->has('username') && $errors->has('password'))
          <li>Both username and password are incorrect.</li>
        @elseif($errors->has('username'))
          <li>Username is incorrect.</li>
        @elseif($errors->has('password'))
          <li>Password is incorrect.</li>
        @else
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        @endif
      </ul>
    </div>
    <button type="button" class="btn-close ms-2" style="filter: invert(0);" onclick="dismissAlert('error-alert')"></button>
  </div>
=======
  {{-- ✅ 登录成功弹窗 --}}
  @if (session('success'))
  <div id="success-alert"
       class="alert alert-success border-0 shadow-sm position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-3 text-center fade-out"
       style="z-index: 1050; min-width: 300px; max-width: 90%; border-radius: 8px; background-color: #73aa75; color: #fff;">
    {{ session('success') }}
    <div class="countdown-text small mt-2 text-light">
      Redirecting in <span id="countdown">3</span> seconds...
      <button class="btn btn-sm btn-outline-light ms-2 py-0 px-2" onclick="window.location.href='{{ route('dashboard') }}'">Go Now</button>
    </div>
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" onclick="dismissAlert('success-alert')"></button>
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
        successAlert.classList.add('hide');
        setTimeout(() => window.location.href = "{{ route('dashboard') }}", 1000);
      }
    }, 1000);
  </script>
  @endif

  {{-- ❌ 登录失败弹窗 --}}
  @if ($errors->any())
  <div id="error-alert"
       class="alert alert-danger border-0 shadow-sm position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-3 text-center fade-out"
       style="z-index: 1050; min-width: 300px; max-width: 90%; border-radius: 8px; background-color: #c75555; color: #fff;">
    <strong>Login failed:</strong>
    <ul class="mt-2 text-start small mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" onclick="dismissAlert('error-alert')"></button>
  </div>

>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
  <script>
    const errorAlert = document.getElementById('error-alert');
    setTimeout(() => {
      if (errorAlert) {
        errorAlert.classList.add('hide');
        setTimeout(() => errorAlert.remove(), 1000);
      }
    }, 3000);
  </script>
  @endif

  {{-- ℹ️ 登出成功弹窗 --}}
  @if (session('logout_success'))
  <div id="logout-alert"
       class="alert alert-info border-0 shadow-sm position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-3 text-center fade-out"
       style="z-index: 1050; min-width: 300px; max-width: 90%; border-radius: 8px; background-color: #3498db; color: #fff;">
    {{ session('logout_success') }}
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" onclick="dismissAlert('logout-alert')"></button>
  </div>
<<<<<<< HEAD
=======

>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
  <script>
    const logoutAlert = document.getElementById('logout-alert');
    setTimeout(() => {
      if (logoutAlert) {
        logoutAlert.classList.add('hide');
        setTimeout(() => logoutAlert.remove(), 1000);
      }
    }, 3000);
  </script>
  @endif

  {{-- 📄 登录表单 --}}
  <div class="col-md-5 col-lg-4">
    <div class="card">
      <div class="card-header">
        <h3>Login</h3>
      </div>
      <div class="card-body px-4 py-4">
        <form method="POST" action="{{ route('login') }}">
          @csrf

          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror"
                   id="username" name="username" value="{{ old('username') }}" required>
            @error('username')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" class="form-control @error('password') is-invalid @enderror"
                     id="password" name="password" required>
              <span class="input-group-text" onclick="togglePassword()">
                <i class="bi bi-eye" id="eye-icon"></i>
              </span>
            </div>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Login</button>
          </div>
        </form>

        <div class="text-center mt-3">
          <p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
        </div>
      </div>
    </div>
  </div>

  <script>
    function dismissAlert(id) {
      const el = document.getElementById(id);
      if (el) {
        el.classList.add('hide');
        setTimeout(() => el.remove(), 1000);
      }
    }

    function togglePassword() {
      const input = document.getElementById('password');
      const icon = document.getElementById('eye-icon');
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
<<<<<<< HEAD
=======

>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
