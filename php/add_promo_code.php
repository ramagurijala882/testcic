<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo Code Management</title>
    <script>
        // Function to load all promo codes and populate the table
    // Update the loadPromoCodes function to properly reflect status in the table
    async function loadPromoCodes() {
        try {
            const response = await fetch('../php/apply_promo_code.php');
            const data = await response.json();

            if (data.success) {
                const tbody = document.querySelector('#promo-table tbody');
                tbody.innerHTML = data.codes.map(code => `
                    <tr>
                        <td>${code.promo_code_id}</td>
                        <td>${code.code}</td>
                        <td>${code.discount_percentage}%</td>
                        <td>${code.is_active == 1 ? 'Active' : 'Inactive'}</td>
                        <td>
                            <button onclick="editPromo(${code.promo_code_id})">Edit</button>
                            <button onclick="deletePromo(${code.promo_code_id})">Delete</button>
                            <button onclick="toggleStatus(${code.promo_code_id}, ${code.is_active == 1 ? 0 : 1})">
                                ${code.is_active == 1 ? 'Deactivate' : 'Activate'}
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                alert('Failed to load promo codes.');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }




        // Add a new promo code
        async function addPromo() {
            const code = document.getElementById('promo-code').value.trim();
            const discount = parseFloat(document.getElementById('discount').value);

            if (!code || isNaN(discount) || discount <= 0) {
                alert('Please provide valid promo code and discount.');
                return;
            }

            try {
                const response = await fetch('../php/apply_promo_code.php?action=add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code, discount })
                });

                const result = await response.json();
                if (result.success) {
                    alert('Promo code added successfully.');
                    document.getElementById('promo-code').value = '';
                    document.getElementById('discount').value = '';
                    loadPromoCodes();  // Reload promo codes after adding
                } else {
                    alert(result.message || 'Failed to add promo code.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Edit a promo code
        async function editPromo(id) {
            const code = prompt('Enter new promo code:');
            const discount = parseFloat(prompt('Enter new discount percentage:'));

            if (!code || isNaN(discount) || discount <= 0) {
                alert('Invalid input.');
                return;
            }

            try {
                const response = await fetch('../php/apply_promo_code.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, code, discount })
                });

                const result = await response.json();
                if (result.success) {
                    alert('Promo code updated successfully.');
                    loadPromoCodes();  // Reload promo codes after updating
                } else {
                    alert(result.message || 'Failed to update promo code.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Delete a promo code
        async function deletePromo(id) {
            if (!confirm('Are you sure you want to delete this promo code?')) return;

            try {
                const response = await fetch(`../php/apply_promo_code.php?action=delete&id=${id}`, { method: 'DELETE' });
                const result = await response.json();

                if (result.success) {
                    alert('Promo code deleted successfully.');
                    loadPromoCodes();  // Reload promo codes after deleting
                } else {
                    alert(result.message || 'Failed to delete promo code.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Toggle the status of a promo code
        async function toggleStatus(id, currentStatus) {
            const newStatus = currentStatus ? 0 : 1;  // Toggle the status (1 -> 0 or 0 -> 1)

            try {
                const response = await fetch('../php/apply_promo_code.php?action=toggle_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, is_active: newStatus })
                });

                const result = await response.json();
                console.log("Response from backend:", result);  // Debugging output

                if (result.success) {
                    alert('Promo code status updated successfully.');
                    loadPromoCodes(); // Reload the promo codes list after updating the status
                } else {
                    alert(result.message || 'Failed to update status.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }


        // Load promo codes when the page is loaded
        window.onload = loadPromoCodes;
    </script>
</head>
<body>
    <h1>Promo Code Management</h1>

    <!-- Add Promo Code Section -->
    <div>
        <h3>Add Promo Code</h3>
        <input type="text" id="promo-code" placeholder="Enter Promo Code">
        <input type="number" id="discount" placeholder="Enter Discount Percentage">
        <button onclick="addPromo()">Add Promo Code</button>
    </div>

    <!-- Promo Codes Table -->
    <div>
        <h3>Existing Promo Codes</h3>
        <table id="promo-table" border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Discount (%)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Promo codes will be dynamically loaded here -->
            </tbody>
        </table>
    </div>
</body>
</html>


