<?php
require_once 'auth_check.php';
requireLogin(); // Protect page with authentication session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell Beauty Clinic - Online Booking</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container" style="max-width: 600px;">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="margin: 0;">GlowWell Beauty Clinic</h1>
            <p style="margin: 5px 0 0 0; color: #666;">Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!</p>
        </div>
        <div>
            <?php if ($_SESSION['role_name'] === 'Admin'): ?>
                <a href="admin.php" style="font-size: 0.9em; text-decoration: none; color: var(--primary-color); font-weight: bold; margin-right: 15px;">Admin Portal &rarr;</a>
            <?php endif; ?>
            <a href="logout.php" style="font-size: 0.9em; text-decoration: none; color: var(--danger-color); font-weight: bold;">Logout</a>
        </div>
    </header>
    <hr><br>

    <div id="feedback" class="alert"></div>

    <section>
        <h2>Book Your Appointment</h2>
        <form id="bookingForm">
            <!-- Hidden field uses the authenticated user's ID automatically -->
            <input type="hidden" id="customer_id" value="<?= htmlspecialchars($_SESSION['customer_id'] ?? 1) ?>">

            <div class="form-group">
                <label for="staff_id">Select Specialist</label>
                <select id="staff_id" required>
                    <option value="">Loading specialists...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="service_id">Select Treatment</label>
                <select id="service_id" required>
                    <option value="">Loading treatments...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="appointment_date">Preferred Date</label>
                <input type="date" id="appointment_date" required>
            </div>
            
            <div class="form-group">
                <label for="start_time">Time Slot</label>
                <input type="time" id="start_time" required>
            </div>
            
            <button type="submit" style="padding: 12px; font-size: 1.05em;">Confirm & Book Appointment</button>
        </form>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Prevent picking past dates
    document.getElementById('appointment_date').min = new Date().toISOString().split('T')[0];

    // Load dynamic dropdown options from backend
    loadFormOptions();

    // Form Submission Logic
    document.getElementById('bookingForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const payload = {
            customer_id: document.getElementById('customer_id').value,
            staff_id: document.getElementById('staff_id').value,
            service_id: document.getElementById('service_id').value,
            appointment_date: document.getElementById('appointment_date').value,
            start_time: document.getElementById('start_time').value
        };

        try {
            const res = await fetch('api/appointments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (res.ok) {
                showAlert(data.message, 'success');
                document.getElementById('bookingForm').reset();
            } else {
                showAlert(data.message, 'error');
            }
        } catch (err) {
            showAlert('Server connection error. Please try again.', 'error');
        }
    });
});

// Fetch Staff and Services dynamically
async function loadFormOptions() {
    try {
        const res = await fetch('api/options.php');
        const data = await res.json();

        if (data.status === 'success') {
            // Populate Specialists
            const staffSelect = document.getElementById('staff_id');
            staffSelect.innerHTML = '<option value="">-- Choose Specialist --</option>';
            data.staff.forEach(s => {
                staffSelect.innerHTML += `<option value="${s.staff_id}">${escapeHtml(s.name)} (${escapeHtml(s.specialization)})</option>`;
            });

            // Populate Services
            const serviceSelect = document.getElementById('service_id');
            serviceSelect.innerHTML = '<option value="">-- Choose Treatment --</option>';
            data.services.forEach(srv => {
                serviceSelect.innerHTML += `<option value="${srv.service_id}">${escapeHtml(srv.service_name)} (R${parseFloat(srv.price).toFixed(2)} - ${srv.duration_minutes} min)</option>`;
            });
        }
    } catch (err) {
        // Fallback options if options API is unavailable
        document.getElementById('staff_id').innerHTML = '<option value="1">Jane Doe (Skin Specialist)</option>';
        document.getElementById('service_id').innerHTML = `
            <option value="1">HydraFacial Glow (R650.00 - 60 min)</option>
            <option value="2">Deep Tissue Massage (R800.00 - 90 min)</option>
            <option value="3">Keratin Hair Treatment (R1200.00 - 120 min)</option>
        `;
    }
}

function showAlert(msg, type) {
    const el = document.getElementById('feedback');
    el.className = `alert alert-${type}`;
    el.innerText = msg;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}

function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}
</script>

</body>
</html>