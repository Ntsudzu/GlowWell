<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell - Customer Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container" style="max-width: 500px; margin-top: 30px;">
    <h2>Create Customer Account</h2>
    <p style="color: #666;">Register to book online spa & beauty services</p>
    <hr><br>

    <div id="feedback" class="alert"></div>

    <form id="registerForm">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" required placeholder="+27...">
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password (Min 6 chars)</label>
            <input type="password" id="password" required minlength="6">
        </div>

        <button type="submit" style="padding: 12px; font-size: 1.05em;">Register Account</button>
    </form>

    <br>
    <p style="text-align: center; font-size: 0.9em;">
        Already registered? <a href="login.php" style="color: var(--primary-color); font-weight: bold;">Log In Here</a>
    </p>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const payload = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };

    try {
        const res = await fetch('api/auth.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (res.ok) {
            showAlert(data.message, 'success');
            setTimeout(() => { window.location.href = 'login.php'; }, 1500);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        showAlert('Registration service error.', 'error');
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