<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prefect of Discipline - Login</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pod-styles.css') }}">
</head>
<body class="auth-page login-page">

    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('ac_logo.png') }}" alt="Aldersgate Logo">
            <h2>Aldersgate College Inc.</h2>
            <p>Prefect of Discipline System</p>
        </div>

        <div class="login-body-inner">

            @if(session('success'))
                <div class="alert-success">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Role Selection Toggle -->
            <div class="role-toggle">
                <button type="button" class="role-btn active" id="btn-teacher" onclick="selectRole('teacher')">
                    👨‍🏫 Teacher / Admin
                </button>
                <button type="button" class="role-btn" id="btn-student" onclick="selectRole('student')">
                    🎓 Student
                </button>
            </div>

            <form method="POST" action="/login">
                @csrf
                <!-- Hidden Field to pass role -->
                <input type="hidden" name="user_type" id="user_type" value="teacher">

                <!-- Dynamic ID / Email Field -->
                <div class="form-group">
                    <label for="identifier_input" id="identifier_label">Email Address</label>
                    <input 
                        type="text" 
                        id="identifier_input" 
                        name="email" 
                        placeholder="admin@aldersgate.edu.ph" 
                        required
                    >
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••" 
                        required
                    >
                </div>

                <!-- Forgot Password Field -->
                <div style="text-align: right; margin-top: 8px; margin-bottom: 16px;">
                    <a href="{{ route('password.request') }}" style="font-size: 0.825rem; font-weight: 600; color: #005a36; text-decoration: none;">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="login-footer">
                © {{ date('Y') }} Francis Robert C. Gecobe. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        function selectRole(role) {
            const btnTeacher = document.getElementById('btn-teacher');
            const btnStudent = document.getElementById('btn-student');
            const userTypeInput = document.getElementById('user_type');
            const label = document.getElementById('identifier_label');
            const input = document.getElementById('identifier_input');

            if (role === 'teacher') {
                btnTeacher.classList.add('active');
                btnStudent.classList.remove('active');
                userTypeInput.value = 'teacher';

                label.innerText = 'Email Address';
                input.name = 'email';
                input.placeholder = 'admin@aldersgate.edu.ph';
                input.type = 'email';
            } else {
                btnStudent.classList.add('active');
                btnTeacher.classList.remove('active');
                userTypeInput.value = 'student';

                label.innerText = 'Student ID Number or Gmail';
                input.name = 'student_number';
                input.placeholder = 'e.g. 23-0537 or name@gmail.com';
                input.type = 'text';
            }
        }
    </script>
</body>
</html>