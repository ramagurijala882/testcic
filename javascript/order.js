async function fetchOrderDetails() {
    const orderId = document.getElementById('order-id').value;
    if (!orderId) {
        alert("Please enter a valid Order ID");
        return;
    }

    try {
        const response = await fetch('../php/fetch_order.php?order_id=' + orderId);

        const order = await response.json();

        if (order && order.orderDetails && Array.isArray(order.orderDetails)) {
            displayOrderDetails(order);
        } else {
            alert("Order not found or missing details.");
        }
    } catch (error) {
        console.error("Error fetching order details:", error);
    }
}

function displayOrderDetails(order) {
    const orderInfo = document.getElementById('order-info');
    const totalPrice = parseFloat(order.total_price) || 0;

    orderInfo.innerHTML = `
        <h2>Order #${order.order_id}</h2>
        <ul>
            ${order.orderDetails.map(product => `
                <li>
                    <strong>${product.product_name}</strong> - ${product.quantity} pcs - $${product.price} each
                    <p>Toppings: ${product.toppings || "None"}</p>
                </li>
            `).join('')}
        </ul>
        <h3>Grand Total: $<span id="totalPrice">${totalPrice.toFixed(2)}</span></h3>
    `;

    document.getElementById('promo-section').style.display = 'block';
    document.getElementById('payment-section').style.display = 'none';
}

async function applyPromoCode() {
    const promoCode = document.getElementById('promo-code').value.trim();
    const totalPrice = parseFloat(document.getElementById('totalPrice').textContent) || 0;
    const orderId = document.getElementById('order-id').value; // Include order ID

    if (!promoCode) {
        alert("Please enter a promo code.");
        return;
    }

    try {
        const response = await fetch('../php/apply_promo_code.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ promoCode, totalPrice, orderId }) // Send orderId
        });

        const result = await response.json();

        if (result.success) {
            const discountedPrice = result.discountedPrice;
            document.getElementById('totalPrice').textContent = discountedPrice.toFixed(2);
            alert(`Promo code applied! New Total: $${discountedPrice.toFixed(2)}`);
        } else {
            alert(result.message || "Failed to apply promo code.");
        }
    } catch (error) {
        console.error("Error applying promo code:", error);
    }
}


function cancelPromoCode() {
    document.getElementById('promo-code').value = '';
    document.getElementById('promo-section').style.display = 'none';
    document.getElementById('payment-section').style.display = 'block';
}

async function makePayment() {
    const orderId = document.getElementById('order-id').value;
    const paymentAmount = parseFloat(document.getElementById('payment-amount').value);
    const totalPrice = parseFloat(document.getElementById('totalPrice').textContent);

    if (!paymentAmount || paymentAmount <= 0) {
        alert("Please enter a valid payment amount");
        return;
    }

    try {
        const response = await fetch('../php/process_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orderId, paymentAmount, totalPrice })
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('payment-info').innerHTML = `
                <p>Change: $${result.change.toFixed(2)}</p>
                

                
                <p>Status: ${result.paymentStatus}</p>
            `;
            document.getElementById('payment-info').style.display = 'block';
        } else {
            alert(result.message || "Error processing payment.");
        }
    } catch (error) {
        console.error("Error making payment:", error);
    }
}


{/* <p>Remaining Balance: $${result.remainingBalance.toFixed(2)}</p> */}