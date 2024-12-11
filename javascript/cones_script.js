let products = [];
let cart = [];

async function fetchCones() {
    try {
        const response = await fetch('../php/fetch_cones.php');
        products = await response.json();
        displayProducts(products);
    } catch (error) {
        console.error('Error fetching cones:', error);
    }
}

function displayProducts(filteredProducts) {
    const container = document.getElementById('cone-products');
    container.innerHTML = '';

    filteredProducts.forEach(product => {
        const productDiv = document.createElement('div');
        productDiv.classList.add('box2');
        
        // Ensure the path to the images is correct
        const imagePath = product.image_path.startsWith('./') ? product.image_path.slice(2) : product.image_path;
        
        // Use the correct relative path to images
        productDiv.innerHTML = `
            <div class="image-checklist">
                <!-- Correct image path with relative URL -->
                <img src="../images/${imagePath}" alt="${product.name}" />
                <div class="checklist">
                    ${product.toppings.split(', ').map(topping => `<label><input type="checkbox" name="${product.name}-${topping}"> ${topping}</label><br>`).join('')}
                </div>
            </div>
            <h1>${product.name}</h1>
            <div class="quantity-control">
                <button onclick="decreaseQuantity('${product.name}')">-</button>
                <span id="quantity-${product.name.replace(/\s+/g, '')}" class="quantity">0</span>
                <button onclick="increaseQuantity('${product.name}', ${product.price})">+</button>
            </div>
            <button class="add-to-cart" onclick="addToCart('${product.name}', ${product.price})">Add to Cart</button>
        `;
        container.appendChild(productDiv);
    });
}




function searchProducts() {
    const searchTerm = document.getElementById('search-bar').value.toLowerCase();
    const products = document.querySelectorAll('#cone-products > div');

    products.forEach(product => {
        const productName = product.querySelector('h1').innerText.toLowerCase();
        product.style.display = productName.includes(searchTerm) ? 'block' : 'none';
    });
}

function increaseQuantity(productName) {
    const quantityElement = document.getElementById(`quantity-${productName.replace(/\s+/g, '')}`);
    quantityElement.innerText = parseInt(quantityElement.innerText) + 1;
}

function decreaseQuantity(productName) {
    const quantityElement = document.getElementById(`quantity-${productName.replace(/\s+/g, '')}`);
    const quantity = parseInt(quantityElement.innerText);
    if (quantity > 0) {
        quantityElement.innerText = quantity - 1;
    }
}

function addToCart(productName, productPrice) {
    const quantity = parseInt(document.getElementById(`quantity-${productName.replace(/\s+/g, '')}`).innerText);
    const selectedToppings = Array.from(document.querySelectorAll(`input[name^="${productName}-"]:checked`)).map(input => input.name.split('-')[1]);

    if (quantity > 0) {
        cart.push({ productName, productPrice, quantity, selectedToppings });
        displayCart();
    } else {
        alert('Please select a quantity.');
    }
}

function displayCart() {
    const cartContainer = document.getElementById('cart');
    cartContainer.innerHTML = ''; 

    let grandTotal = 0;
    // cartContainer.innerHTML = `'<h2>Your cart</h2>'`;

    cart.forEach((item, index) => {
        const itemTotal = (item.productPrice + item.selectedToppings.length * 0.5) * item.quantity;
        grandTotal += itemTotal;

        cartContainer.innerHTML += `
            <div class="cart-section">
                <h2>${item.productName}</h2>
                <p>Price: $${item.productPrice}</p>
                <p>Quantity: ${item.quantity}</p>
                <p>Toppings: ${item.selectedToppings.join(', ')}</p>
                <p>Total: $${itemTotal.toFixed(2)}</p>
                <button class="add-to-cart" onclick="removeFromCart(${index})">Remove</button>
            </div>
        `;
    });

    cartContainer.innerHTML += `<h3 id="grand-total">Grand Total: $${grandTotal.toFixed(2)}</h3>
        <button class="add-to-cart"="checkout-btn" onclick="proceedToCheckout()">Checkout</button>`;    
}

function removeFromCart(index) {
    cart.splice(index, 1);
    displayCart();
}

function proceedToCheckout() {
    sessionStorage.setItem('cart', JSON.stringify(cart));
    window.location.href = '../php/checkout.php';
}

window.onload = fetchCones;
