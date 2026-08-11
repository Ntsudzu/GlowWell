<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container" style="max-width: 450px; margin-top: 50px;">
    <h2>GlowWell Clinic Login</h2>
    <p style="color: #666;">Sign in to manage your appointments</p>
    <hr><br>

    <div id="feedback" class="alert"></div>

    <form id="loginForm">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" required placeholder="admin@glowwell.com">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" required>
        </div>

        <button type="submit" style="padding: 12px; font-size: 1.05em;">Log In</button>
    </form>

    <br>
    <p style="text-align: center; font-size: 0.9em;">
        New customer? <a href="register.php" style="color: var(--primary-color); font-weight: bold;">Create an account</a>
    </p>
    <div style="background:#f0f0f0; padding:10px; border-radius:6px; font-size:0.85em; margin-top:15px;">
        <strong>Default Test Credentials:</strong><br>
        Admin: <code>admin@glowwell.com</code> / <code>password123</code><br>
        Customer: <code>alice.smith@example.com</code> / <code>password123</code>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const payload = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };

    try {
        const res = await fetch('api/auth.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (res.ok) {
            showAlert(data.message, 'success');
            setTimeout(() => { window.location.href = data.redirect; }, 1000);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        showAlert('Network or server error.', 'error');
    }
});

function showAlert(msg, type) {
    const el = document.getElementById('feedback');
    el.className = `alert alert-${type}`;
    el.innerText = msg;
    el.style.display = 'block';
}
</script>

</body>
</html>