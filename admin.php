<?php
require_once 'auth_check.php';
requireRole(['Admin']); // Blocks regular customers with 403 Forbidden
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="margin: 0;">Admin Management Portal</h1>
            <p style="margin: 5px 0 0 0; color: #666;">Live Appointments, Schedule Monitoring & Reports</p>
        </div>
        <a href="index.php" style="font-size: 0.9em; text-decoration: none; color: var(--primary-color); font-weight: bold;">&larr; Customer Booking</a>
    </header>
    <hr><br>

    <div id="feedback" class="alert"></div>

    <!-- Summary Metrics / Report Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <div style="background: var(--secondary-color); padding: 15px; border-radius: 8px; border-left: 5px solid var(--primary-color);">
            <h4 style="margin: 0; color: #666;">Total Bookings</h4>
            <p id="metricTotal" style="font-size: 1.8em; font-weight: bold; margin: 5px 0 0 0;">0</p>
        </div>
        <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; border-left: 5px solid var(--success-color);">
            <h4 style="margin: 0; color: #666;">Projected Revenue</h4>
            <p id="metricRevenue" style="font-size: 1.8em; font-weight: bold; margin: 5px 0 0 0; color: var(--success-color);">R0.00</p>
        </div>
        <div style="background: #ffebee; padding: 15px; border-radius: 8px; border-left: 5px solid var(--danger-color);">
            <h4 style="margin: 0; color: #666;">Cancelled Bookings</h4>
            <p id="metricCancelled" style="font-size: 1.8em; font-weight: bold; margin: 5px 0 0 0; color: var(--danger-color);">0</p>
        </div>
    </div>

    <!-- Live Appointment Table -->
    <section>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h2>Live Appointments Schedule</h2>
            <button onclick="loadDashboardData()" style="width: auto; padding: 6px 12px; font-size: 0.9em;">Refresh Schedule</button>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Staff Specialist</th>
                    <th>Service</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="appointmentTableBody">
                <!-- Data populated dynamically via JS -->
            </tbody>
        </table>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();
});

async function loadDashboardData() {
    try {
        const res = await fetch('api/appointments.php');
        const result = await res.json();
        
        const tbody = document.getElementById('appointmentTableBody');
        tbody.innerHTML = '';

        if (result.status === 'success') {
            let total = result.data.length;
            let revenue = 0;
            let cancelled = 0;

            result.data.forEach(item => {
                // Metrics Calculation
                if (item.status === 'Cancelled') {
                    cancelled++;
                } else {
                    revenue += parseFloat(item.price);
                }

                // Table Rows Rendering
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>#${item.appointment_id}</td>
                    <td>${escapeHtml(item.customer_name)}</td>
                    <td>${escapeHtml(item.staff_name)}</td>
                    <td>${escapeHtml(item.service_name)} (R${parseFloat(item.price).toFixed(2)})</td>
                    <td>${item.appointment_date} at ${item.start_time}</td>
                    <td><span class="badge badge-${item.status}">${item.status}</span></td>
                    <td>
                        ${item.status !== 'Cancelled' ? `<button style="background:var(--danger-color); padding: 4px 8px; font-size:12px; width:auto;" onclick="cancelAppointment(${item.appointment_id})">Cancel</button>` : '-'}
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update Summary Cards
            document.getElementById('metricTotal').innerText = total;
            document.getElementById('metricRevenue').innerText = `R${revenue.toFixed(2)}`;
            document.getElementById('metricCancelled').innerText = cancelled;
        }
    } catch (err) {
        showAlert('Failed to connect to administrative data service.', 'error');
    }
}

async function cancelAppointment(id) {
    if (!confirm(`Are you sure you want to cancel appointment #${id}?`)) return;

    try {
        const res = await fetch(`api/appointments.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (res.ok) {
            showAlert(data.message, 'success');
            loadDashboardData();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        showAlert('Failed to process cancellation request.', 'error');
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