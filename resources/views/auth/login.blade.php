<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول | نظام إدارة العقارات</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        /* استخدام نفس المتغيرات لضمان تناسق النظام */
        :root {
            --bg-body: #f4f7fe;
            --bg-card: #ffffff;
            --text-main: #2b3674;
            --text-light: #a3aed0;
            --accent: #4318ff;
            --border-color: #e0e5f2;
            --shadow: 0px 20px 50px rgba(0, 0, 0, 0.1);
        }

        body {
            background: var(--bg-body);
            background-image: radial-gradient(circle at 10% 20%, rgba(67, 24, 255, 0.05) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(67, 24, 255, 0.05) 0%, transparent 40%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Tajawal', sans-serif;
            margin: 0;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        /* لمسة ديكور علوية */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 5px;
            background: var(--accent);
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            background: var(--bg-body);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: var(--accent);
            font-size: 1.8rem;
            border: 1px solid var(--border-color);
        }

        .login-card h3 {
            color: var(--text-main);
            font-weight: 800;
            text-align: center;
            margin-bottom: 8px;
        }

        .login-card p.subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-label {
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 8px;
            margin-right: 5px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            background-color: var(--bg-body);
            transition: 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.1);
            border-color: var(--accent);
            background-color: #fff;
        }

        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .btn-login {
            background: var(--accent);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #3612d3;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 24, 255, 0.2);
            color: #fff;
        }

        .alert {
            border-radius: 12px;
            font-size: 0.85rem;
            border: none;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="brand-logo">
            <i class="fas fa-city"></i>
        </div>

        <h3>مرحباً بك مجدداً</h3>
        <p class="subtitle">أدخل بياناتك للوصول إلى لوحة التحكم</p>

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-exclamation-circle me-1"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success shadow-sm">
                <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@company.com" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <div class="position-relative">
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items: center; mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label text-secondary" for="remember" style="font-size: 0.85rem;">تذكرني</label>
                </div>
                <a href="#" class="text-decoration-none" style="color: var(--accent); font-size: 0.85rem; font-weight: 600;">نسيت كلمة المرور؟</a>
            </div>

            <button type="submit" class="btn btn-login w-100">
                تسجيل الدخول <i class="fas fa-arrow-left ms-2" style="font-size: 0.8rem;"></i>
            </button>
        </form>
    </div>
    
    <p class="text-center mt-4" style="color: var(--text-light); font-size: 0.85rem;">
        &copy; 2026 جميع الحقوق محفوظة لشركتك العقارية
    </p>
</div>

</body>
</html>