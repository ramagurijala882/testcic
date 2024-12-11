<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Five Box Layout</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
        <div class="menu">
            <h2><a href="admin.html">Admin</a></h2>
        </div>

        <header class="header">
            <img src="../images/IcPlogo.jpeg" alt="Logo" class="logo">
            <h1><a href="../index.html" class="header-title">Cool Summer Icecreams</a></h1>
        </header>

        <div class="layout">
            <aside class="sidebar left-sidebar">
                <h2>Promotions</h2>
                <img src="../images/discounts/apply_discount.gif" alt="Discount">
                <br>
                <img src="../images/poster.png" alt="Sidebar Image" class="sidebar-image">
            </aside>

            <main class="main-content">
                <h2>Order Summary</h2>
                <br>
                <div class="container">
                    <div class="box2">
                        <div id="order-summary"></div>
                    

                    <button class="add-to-cart button-pay" id="proceed-to-pay">Proceed to Pay</button> </div>

                    <div class="box2">
                        <div id="customer-form" style="display:none;">
                            <form id="customerDetailsForm">
                                <h1>Customer Details</h1>
                                <label>Name: <input type="text" name="name" required></label><br>
                                <label>Phone: <input type="tel" name="phone" required pattern="[0-9]{10}" title="Enter a 10-digit phone number"></label><br>
                                <button class="add-to-cart button-pay" id="submit-order">Submit Order</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <footer>
            <h1>&copy; 2024 Cool Summer Icecreams. All rights reserved.</h1>
        </footer>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let grandTotal = 0;
                const cartData = JSON.parse(sessionStorage.getItem('cart') || '[]'); // Safely handle empty cart
                const orderSummary = document.getElementById('order-summary');

                if (cartData.length > 0) {
                    cartData.forEach(item => {
                        const toppingTotal = item.selectedToppings.length * 0.5;
                        const itemTotal = (item.productPrice * item.quantity) + (toppingTotal * item.quantity);

                        orderSummary.innerHTML += `
                            <article>
                                <h3>${item.productName}</h3>
                                <p>Quantity: ${item.quantity}</p>
                                <p>Price per item: $${item.productPrice}</p>
                                <p>Toppings: ${item.selectedToppings.join(', ')}</p>
                                <p>Topping Charge: $${(toppingTotal * item.quantity).toFixed(2)}</p>
                                <p>Total: $${itemTotal.toFixed(2)}</p>
                            </article>
                            <hr>
                        `;

                        grandTotal += itemTotal;
                    });

                    // Display the grand total
                    orderSummary.innerHTML += `<strong id="grandTotal">Grand Total: $${grandTotal.toFixed(2)}</strong>`;
                } else {
                    orderSummary.innerHTML = "<p>No items in the cart.</p>";
                }

                // Add event listener to show the customer form
                document.getElementById('proceed-to-pay').addEventListener('click', () => {
                    document.getElementById('customer-form').style.display = 'block';
                });

                // Add event listener for the order submission
                document.getElementById('submit-order').addEventListener('click', async (event) => {
                    event.preventDefault();

                    const form = document.getElementById('customerDetailsForm');
                    if (!form.checkValidity()) {
                        alert("Please fill in all fields");
                        return;
                    }

                    const formData = new FormData(form);
                    const grandTotal = document.getElementById('grandTotal').textContent.replace('Grand Total: $', '');

                    formData.append('cart', JSON.stringify(cartData));
                    formData.append('grandTotal', grandTotal);

                    try {
                        const response = await fetch('../php/submit_order.php', {
                            method: 'POST',
                            body: formData,
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const result = await response.json();
                        console.log(result); // Debugging log

                        if (result.success) {
                            alert(`Order placed successfully! Your Order ID is: ${result.order_id}`);
                            sessionStorage.removeItem('cart');
                            window.location.href = '../index.html';
                        } else {
                            alert('Error placing order');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An unexpected error occurred. Please try again later.');
                    }
                });
            });
        </script>
    </body>
</html>
