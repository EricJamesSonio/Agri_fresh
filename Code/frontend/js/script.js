class ShoppingCart {
  constructor() {
    this.products = [];
    this.cart = [];
    this.activeTags = [];
    this.customer_id = this.initializeCustomerId();
  }

  
  initializeCustomerId() {
    const rawCustomerId = localStorage.getItem("customer_id");
    const customerName = localStorage.getItem("customer_name");

    console.log("Raw customer_id from localStorage:", rawCustomerId);
    console.log("Customer name from localStorage:", customerName);

    // 🚫 If the logged in user is admin, block cart
    if (customerName && customerName.toLowerCase().includes("admin")) {
      console.warn("Admin detected, disabling cart features.");
      return null;
    }

    if (rawCustomerId && rawCustomerId !== "null" && rawCustomerId !== "undefined") {
      const parsedId = parseInt(rawCustomerId, 10);
      console.log("Parsed customer_id:", parsedId);

      if (isNaN(parsedId)) {
        console.warn("customer_id is not a valid number:", rawCustomerId);
        return null;
      }
      return parsedId;
    } else {
      console.warn("No valid customer_id found. Cart actions will be local only.");
      return null;
    }
  }
  async fetchProducts() {
    try {
      console.log("Fetching products from:", apiUrl('products'));
      const res = await fetch(apiUrl('products'));
      if (!res.ok) throw new Error('Failed to fetch products');

      const rawProducts = await res.json();
      console.log("Raw products from API:", rawProducts);
      
      this.products = rawProducts.map(p => {
        console.log("Processing product:", p);
        return {
          ...p,
          // Convert id to integer to prevent string concatenation issues
          id: parseInt(p.id, 10),
          img: `${CONFIG.IMAGE_PATH}/${p.img}`
        };
      });

      console.log("Final products array:", this.products);
      this.render(this.products);
    } catch (err) {
      console.error(err);
      document.getElementById('products').innerHTML =
        `<p style="color:red;">Failed to load products</p>`;
    }
  }

  render(list = this.products) {
    const grid = document.getElementById('products');
    
    if (!grid) {
      console.error("Products grid element not found!");
      return;
    }
    
    grid.innerHTML = list.map(p => {
      const productId = p.id;
      
      if (!productId) {
        console.error("No id found for product:", p);
        return '';
      }
      
      return `
      <div class="card">
        <img src="${p.img || p.image_url}" alt="${p.name}" loading="lazy" onerror="this.src='../images/placeholder.jpg'">
        <div class="card-body">
          <h4>${p.name}</h4>
          <span class="price">₱${p.price}</span>
          <button class="add-to-cart-btn" data-product-id="${productId}" onclick="cartInstance.addToCart(${productId})">Add to Cart</button>
        </div>
      </div>
    `;
    }).join('');
  }

  async addToCart(product_id) {
  console.log("=== ADD TO CART DEBUG ===");
  console.log("addToCart called with product_id:", product_id, typeof product_id);
  console.log("Current customer_id:", this.customer_id, typeof this.customer_id);

  // 🚫 Prevent admin from adding to cart
  const customerName = localStorage.getItem("customer_name");
  if (customerName && customerName.toLowerCase().includes("admin")) {
    alert("Admins are not allowed to add items to the cart.");
    return;
  }

  // Ensure product_id is an integer
  product_id = parseInt(product_id, 10);

  // Force refresh customer_id from localStorage
  let fresh_customer_id = localStorage.getItem("customer_id");
  if (fresh_customer_id && fresh_customer_id !== "null") {
    fresh_customer_id = parseInt(fresh_customer_id, 10);
    if (!isNaN(fresh_customer_id)) {
      this.customer_id = fresh_customer_id;
      console.log("Refreshed customer_id:", this.customer_id);
    }
  }

  // If user is not logged in
  if (!this.customer_id || isNaN(this.customer_id)) {
    alert("You are not logged in yet. Please login to add items to your cart.");
    window.location.href = 'login.html'; // redirect to login page
    return;
  }

  try {
    // Existing code to add to cart if logged in...
    const found = this.cart.find(i => parseInt(i.product_id, 10) === product_id);
    const currentQty = found ? parseInt(found.qty, 10) : 0;
    const totalQty = currentQty + 1;

    const payload = {
      customer_id: parseInt(this.customer_id, 10),
      product_id: parseInt(product_id, 10),
      quantity: totalQty
    };

    const response = await fetch(apiUrl('cart'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    if (!response.ok || result.status === 'error') {
      throw new Error(result.message || 'Failed to add to cart');
    }

    await this.fetchCart();
  } catch (err) {
    console.error('Failed to sync cart:', err);
    alert('Failed to add item to cart: ' + err.message);
  }
}


  addToLocalCart(product_id) {
    const product = this.products.find(p => p.id === product_id);
    const found = this.cart.find(i => parseInt(i.product_id, 10) === product_id);
    
    if (found) {
      found.qty = parseInt(found.qty, 10) + 1; // FIXED: Ensure proper integer addition
    } else {
      this.cart.push({ 
        name: product.name, 
        price: product.price, 
        qty: 1, 
        product_id: product_id 
      });
    }
    this.updateCart();
  }

  updateCart() {
    const list = document.getElementById('cart-items');
    const total = document.getElementById('cart-total');
    const count = document.getElementById('cart-count');

    list.innerHTML = this.cart.map(
      (item, idx) =>
        `<li>
           ${item.name} – ₱${item.price} × ${item.qty}
           <span class="item-controls">
             <button class="minus" onclick="cartInstance.changeQty(${idx},-1)">-</button>
             <button class="plus"  onclick="cartInstance.changeQty(${idx},1)">+</button>
             <button class="remove" onclick="cartInstance.removeItem(${idx})">×</button>
           </span>
         </li>`
    ).join('');

    const grand = this.cart.reduce((t, i) => t + (parseFloat(i.price) * parseInt(i.qty, 10)), 0);
    total.textContent = '₱' + grand;
    count.textContent = this.cart.reduce((c, i) => c + parseInt(i.qty, 10), 0);

    document.getElementById('cart').classList.toggle('show', this.cart.length > 0);
  }

  async changeQty(idx, delta) {
    const item = this.cart[idx];
    const currentQty = parseInt(item.qty, 10);
    const newQty = currentQty + delta;

    if (!this.customer_id) {
      if (newQty < 1) {
        this.cart.splice(idx, 1);
      } else {
        this.cart[idx].qty = newQty;
      }
      this.updateCart();
      return;
    }

    try {
      const payload = {
        customer_id: parseInt(this.customer_id, 10),
        product_id: parseInt(item.product_id, 10),
        quantity: Math.max(newQty, 0)
      };

      const response = await fetch(apiUrl('cart'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!response.ok || result.status === 'error') {
        throw new Error(result.message || 'Failed to update cart');
      }

      await this.fetchCart();
    } catch (err) {
      console.error('Failed to update cart item:', err);
    }
  }

  async removeItem(idx) {
    const product_id = this.cart[idx].product_id;

    if (!this.customer_id) {
      this.cart.splice(idx, 1);
      this.updateCart();
      return;
    }

    try {
      const payload = {
        customer_id: parseInt(this.customer_id, 10),
        product_id: parseInt(product_id, 10),
        quantity: 0
      };

      const response = await fetch(apiUrl('cart'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!response.ok || result.status === 'error') {
        throw new Error(result.message || 'Failed to remove item');
      }

      await this.fetchCart();
    } catch (err) {
      console.error('Failed to remove cart item:', err);
    }
  }

  async fetchCart() {
    if (!this.customer_id) return;

    try {
      const res = await fetch(apiUrl(`cart?customer_id=${this.customer_id}`));
      if (!res.ok) throw new Error('Failed to fetch cart');
      const data = await res.json();
      this.cart = data.map(i => ({
        name: i.name,
        price: parseFloat(i.price_each),
        qty: parseInt(i.quantity, 10), // FIXED: Ensure qty is integer
        product_id: parseInt(i.product_id, 10) // FIXED: Ensure product_id is integer
      }));
      this.updateCart();
    } catch (err) {
      console.error(err);
    }
  }
  logout() {
    console.log("Logging out customer_id:", this.customer_id);
    
    // Remove customer identifier
    localStorage.removeItem("customer_id");

    // Reset internal cart
    this.cart = [];
    this.customer_id = null;

    // Update UI
    this.updateCart();

    // Optionally redirect to homepage/login
    window.location.href = location.origin + "/agri/code/frontend/html/login.html";

  }

  filterProducts() {
    const search = document.getElementById('search').value.toLowerCase();
    const category = document.getElementById('category').value;
    let filtered = this.products.filter(p => {
      const matchSearch = p.name.toLowerCase().includes(search);
      const matchCat = category === 'all' || p.category === category;
      const matchTag = this.activeTags.length === 0 || this.activeTags.every(t => p.tags && p.tags.includes(t));
      return matchSearch && matchCat && matchTag;
    });
    this.render(filtered);
  }

  toggleTag(btn, tag) {
    btn.classList.toggle('active');
    this.activeTags = [...document.querySelectorAll('.tags .active')]
      .map(b => b.textContent.trim());
    this.filterProducts();
  }

  closeCart() {
    document.getElementById('cart').classList.remove('show');
  }

  checkout() {
    if (this.cart.length === 0) {
      alert('Your cart is empty!');
      return;
    }
    
    if (!this.customer_id) {
      alert('Please login to checkout');
      window.location.href = 'login.html';
      return;
    }
    
    // Redirect to checkout address page
    window.location.href = 'checkout-address.html';
  }

  scrollToProducts() {
    document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
  }

  // Debug functions
  debugLocalStorage() {
    console.log("=== LOCALSTORAGE DEBUG ===");
    console.log("All localStorage keys:", Object.keys(localStorage));
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      const value = localStorage.getItem(key);
      console.log(`${key}: ${value} (type: ${typeof value})`);
    }
  }

  async testCartAPI() {
    const testPayload = {
      customer_id: parseInt(localStorage.getItem("customer_id"), 10),
      product_id: 1,
      quantity: 1
    };
    
    console.log("Testing with payload:", testPayload);
    
    try {
      const response = await fetch(apiUrl('cart'), {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(testPayload)
      });
      
      const result = await response.json();
      console.log("Test response:", result);
    } catch (err) {
      console.error("Test failed:", err);
    }
  }
}


// Create global instance to maintain compatibility with existing HTML

const cartInstance = new ShoppingCart();
// Expose logout globally so HTML can call it
function logout() {cartInstance.logout();}window.logout = logout;

// Keep original function names for HTML compatibility
let products = cartInstance.products;
let cart = cartInstance.cart;
let activeTags = cartInstance.activeTags;
let customer_id = cartInstance.customer_id;

// Wrapper functions to maintain HTML compatibility
function fetchProducts() { return cartInstance.fetchProducts(); }
function render(list) { return cartInstance.render(list); }
function addToCart(product_id) { return cartInstance.addToCart(product_id); }
function updateCart() { return cartInstance.updateCart(); }
function changeQty(idx, delta) { return cartInstance.changeQty(idx, delta); }
function removeItem(idx) { return cartInstance.removeItem(idx); }
function closeCart() { return cartInstance.closeCart(); }
function filterProducts() { return cartInstance.filterProducts(); }
function toggleTag(btn, tag) { return cartInstance.toggleTag(btn, tag); }
function fetchCart() { return cartInstance.fetchCart(); }
function checkout() { return cartInstance.checkout(); }
function scrollToProducts() { return cartInstance.scrollToProducts(); }
function debugLocalStorage() { return cartInstance.debugLocalStorage(); }
function testCartAPI() { return cartInstance.testCartAPI(); }

// Make debug functions available globally
window.debugLocalStorage = debugLocalStorage;
window.testCartAPI = testCartAPI;

// Initialize the application
cartInstance.fetchProducts();
cartInstance.fetchCart();