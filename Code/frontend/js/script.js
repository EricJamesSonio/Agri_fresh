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
        let imgSrc = p.img || p.image_url;
        if (!imgSrc) {
          imgSrc = '/agri_fresh/code/frontend/images/placeholder.jpg'; // absolute safe path
        } else if (!imgSrc.startsWith('http')) {
          imgSrc = imageUrl(imgSrc);
        }

        return {
          ...p,
          id: parseInt(p.product_id || p.id, 10),
          category: p.category ?? p.category_name ?? 'Uncategorized',
          description: p.description ?? '',
          tags: Array.isArray(p.tags) ? p.tags.map(t => t.toLowerCase()) : [],
          img: imgSrc,
          stock_quantity: parseInt(p.stock_quantity || 0, 10) // Ensure stock is integer
        };
      });

      console.log("Final products array:", this.products);
      this.render(this.products);
    } catch (err) {
      console.error(err);
      const productsEl = document.getElementById('products');
      if (productsEl) {
        productsEl.innerHTML = `<p style="color:red;">Failed to load products</p>`;
      }
    }
  }

  async populateCategories() {
    const sel = document.getElementById('category');
    if (!sel) return;

    sel.innerHTML = `<option value="all">All Categories</option>`;

    try {
      // NOTE: use 'category' to match your index.php switch-case
      console.log("Fetching categories from:", apiUrl('category'));
      const res = await fetch(apiUrl('category'));
      const text = await res.text();
      console.log("Raw categories response text:", text);

      if (!res.ok) throw new Error(`Status ${res.status}`);

      let cats;
      try {
        cats = JSON.parse(text);
      } catch (e) {
        console.error('Failed to parse categories JSON', e);
        cats = [];
      }

      if (!Array.isArray(cats) || cats.length === 0) {
        const opt = document.createElement('option');
        opt.value = 'none';
        opt.textContent = '— No categories —';
        opt.disabled = true;
        sel.appendChild(opt);
        return;
      }

      cats.forEach(c => {
        const rawName = c.name ?? c.category_name ?? null;
        if (!rawName) {
          console.warn('Skipping category with no name field:', c);
          return;
        }
        const opt = document.createElement('option');
        opt.value = String(rawName).toLowerCase();
        opt.textContent = rawName;
        sel.appendChild(opt);
      });
    } catch (err) {
      console.error('Could not load categories', err);
      const opt = document.createElement('option');
      opt.value = 'all';
      opt.textContent = 'All Categories';
      sel.appendChild(opt);
    }
  }

  render(list = this.products) {
  const grid = document.getElementById('products');
  if (!grid) return;

  // Only keep unique products by name (first occurrence)
  const uniqueProductsMap = new Map();
  list.forEach(p => {
    if (!uniqueProductsMap.has(p.name)) {
      uniqueProductsMap.set(p.name, p);
    }
  });
  const uniqueList = Array.from(uniqueProductsMap.values());

  const safeList = uniqueList.map(p => {
    const img = p.img && p.img.trim() ? p.img : '/agri_fresh/code/frontend/images/placeholder.jpg';
    return { ...p, img };
  });

  grid.innerHTML = safeList.map(p => {
    const isOutOfStock = p.stock_quantity <= 0;

    return `
      <div class="card ${isOutOfStock ? 'out-of-stock' : ''}">
        <div class="card-image-container">
          <img src="${p.img}" alt="${p.name}" loading="lazy"
               onerror="this.onerror=null; this.src='/agri_fresh/code/frontend/images/placeholder.jpg'">
          ${isOutOfStock ? '<div class="out-of-stock-overlay"><span>OUT OF STOCK</span></div>' : ''}
        </div>
        <div class="card-body">
          <h4>${p.name}</h4>
          <span class="price">₱${p.price}</span>
          <button class="add-to-cart-btn ${isOutOfStock ? 'disabled' : ''}" 
                  onclick="addToCart(${p.id})" 
                  ${isOutOfStock ? 'disabled' : ''}>
            ${isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
          </button>
        </div>
      </div>
    `;
  }).join('');

  this.addOutOfStockStyles();
}

  addOutOfStockStyles() {
    // Check if styles are already added
    if (document.getElementById('out-of-stock-styles')) return;

    const styles = document.createElement('style');
    styles.id = 'out-of-stock-styles';
    styles.textContent = `
      .card.out-of-stock {
        opacity: 0.7;
      }
      
      .card-image-container {
        position: relative;
        overflow: hidden;
      }
      
      .out-of-stock-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(128, 128, 128, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
      }
      
      .out-of-stock-overlay span {
        color: white;
        font-weight: bold;
        font-size: 1.2em;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        letter-spacing: 1px;
      }
      
      .add-to-cart-btn.disabled {
        background-color: #ccc !important;
        color: #666 !important;
        cursor: not-allowed !important;
        opacity: 0.6;
      }
      
      .add-to-cart-btn.disabled:hover {
        background-color: #ccc !important;
        transform: none !important;
      }
    `;
    document.head.appendChild(styles);
  }

 async addToCart(product_id) {
  const product = this.products.find(p => p.id === product_id);
  if (!product) return;

  // open modal instead of posting directly
  this.openSizeModal(product);
}

  showStockWarning(remaining) {
    if (remaining > 0) {
      this.showFloatingNotification(`Only ${remaining} left in stock!`);
    }
  }

  showFloatingNotification(message) {
    const notif = document.createElement('div');
    notif.textContent = message;
    notif.style.cssText = `
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #ff9800;
      color: white;
      padding: 12px 16px;
      border-radius: 6px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      z-index: 9999;
      font-weight: bold;
      font-size: 14px;
      transition: opacity 0.3s ease;
    `;
    document.body.appendChild(notif);

    setTimeout(() => {
      notif.style.opacity = '0';
      setTimeout(() => notif.remove(), 300);
    }, 3000);
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
        product_id: product_id ,img: product.img ?? '../images/placeholder.jpg'
      });
    }
    this.updateCart();
  }

  updateCart() {
    // Floating cart (index.php)
    const list = document.getElementById('cart-items');
    const total = document.getElementById('cart-total');
    const count = document.getElementById('cart-count');

    if (list && total && count) {
        list.innerHTML = this.cart.map(
            (item, idx) => {
                const lineTotal = parseFloat(item.price) * parseInt(item.qty, 10);
                return `<li>
                    ${item.name} ${item.size ? `(${item.size})` : ''} – 
                    ${lineTotal.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })} 
                    <span class="item-controls">
                        <button class="minus" onclick="cartInstance.changeQty(${idx},-1)">-</button>
                        <button class="plus"  onclick="cartInstance.changeQty(${idx},1)">+</button>
                        <button class="remove" onclick="cartInstance.removeItem(${idx})">×</button>
                    </span>
                </li>`;
            }
        ).join('');

        const grand = this.cart.reduce((t, i) => t + (parseFloat(i.price) * parseInt(i.qty, 10)), 0);
        total.textContent = grand.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
        count.textContent = this.cart.reduce((c, i) => c + parseInt(i.qty, 10), 0);

        document.getElementById('cart').classList.toggle('show', this.cart.length > 0);
    }

    // Main cart table (my-orders.html)
    if (typeof renderOrderTable === 'function') renderOrderTable();
}

openSizeModal(product) {
  const modal = document.getElementById("sizeModal");
  document.getElementById("modalProductName").textContent = product.name;
  document.getElementById("modalProductImg").src = product.img;
  document.getElementById("modalProductDesc").textContent = product.description;

  const sizeSelect = document.getElementById("sizeSelect");
  sizeSelect.innerHTML = ""; // clear existing options

  // Find all variants of this product (different sizes)
  const variants = this.products.filter(p => p.name === product.name);

  variants.forEach(v => {
    const option = document.createElement("option");
    option.value = `${v.size_value} ${v.size_unit}`;
    option.textContent = `${v.size_value} ${v.size_unit} - ₱${v.price}`;
    sizeSelect.appendChild(option);
  });

  document.getElementById("confirmAddBtn").onclick = () => {
    const qty = parseInt(document.getElementById("modalQty").value, 10) || 1;
    const [size_value, size_unit] = sizeSelect.value.split(" ");
    
    // Find the exact variant by size
    const selectedProduct = variants.find(v => 
      v.size_value == parseFloat(size_value) && v.size_unit === size_unit
    );

    this.confirmAddToCart(selectedProduct, qty);
    this.closeSizeModal();
  };

  modal.classList.remove("hidden");
}


closeSizeModal() {
  document.getElementById("sizeModal").classList.add("hidden");
}

async confirmAddToCart(product, qty) {
  if (!this.customer_id) {
    alert("You are not logged in yet. Please login first.");
    window.location.href = 'login.php';
    return;
  }

  if (qty > product.stock_quantity) {
    alert(`Only ${product.stock_quantity} available in stock.`);
    return;
  }

  const payload = {
    customer_id: parseInt(this.customer_id, 10),
    product_id: parseInt(product.id, 10),
    size_value: product.size_value,
    size_unit: product.size_unit,
    quantity: qty
  };

  try {
    const response = await fetch(apiUrl('cart'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    // Clone response to safely read it if JSON parsing fails
    const respClone = response.clone();
    let result;

    try {
      result = await response.json();
    } catch (jsonErr) {
      // If parsing fails, log raw text
      const rawText = await respClone.text();
      console.error("Invalid JSON from server:", rawText);
      throw new Error("Server returned invalid response.");
    }

    if (!response.ok || result.status === 'error') {
      throw new Error(result.message || "Failed to add to cart");
    }

    await this.fetchCart();

    this.showFloatingNotification(
      `${product.name} (${product.size_value} ${product.size_unit}) added to cart`
    );

  } catch (err) {
    console.error("Add to cart failed:", err);
    alert(err.message || "Failed to add item to cart.");
  }
}


async changeQty(idx, delta) {
  const item = this.cart[idx];
  const currentQty = parseInt(item.qty, 10);
  const newQty = currentQty + delta;

  // Check stock when increasing
  if (delta > 0) {
    const product = this.products.find(p => p.id === parseInt(item.product_id, 10));
    if (product && newQty > product.stock_quantity) {
      alert(`Cannot add more items. Only ${product.stock_quantity} available in stock.`);
      return;
    }
  }

  if (!this.customer_id) {
    // Local-only fallback
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
      quantity: newQty, // send new quantity
      price_each: item.price,
      size_value: item.size ? item.size.split(" ")[0] : null,
      size_unit: item.size ? item.size.split(" ")[1] : null
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
}async removeItem(idx) {
  const item = this.cart[idx];

  if (!this.customer_id) {
    this.cart.splice(idx, 1);
    this.updateCart();
    return;
  }

  try {
    const payload = {
      customer_id: parseInt(this.customer_id, 10),
      product_id: parseInt(item.product_id, 10),
      quantity: 0, // backend interprets this as delete
      price_each: item.price,
      size_value: item.size ? item.size.split(" ")[0] : null,
      size_unit: item.size ? item.size.split(" ")[1] : null
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
this.cart = data.map(i => {
  const prod = this.products.find(p => p.id === parseInt(i.product_id, 10));
  return {
    name: i.name,
    size: `${i.size_value} ${i.size_unit}`, // combine for UI
    price: parseFloat(i.price_each),
    qty: parseInt(i.quantity, 10),
    product_id: parseInt(i.product_id, 10),
    img: prod ? prod.img : imageUrl('placeholder.jpg')
  };
});



      this.updateCart();
    } catch (err) {
      console.error(err);
    }
  }

logout() {
  console.log("Logging out customer_id:", this.customer_id);

  localStorage.clear();


  this.cart = [];
  this.customer_id = null;

  this.updateCart();


  window.location.href = `${location.origin}/agri_fresh/code/frontend/html/login.php`;
}


  filterProducts() {
    const search = (document.getElementById('search').value || '').toLowerCase().trim();
    const category = (document.getElementById('category').value || '').toLowerCase();
    const activeTags = this.activeTags.map(t => t.toLowerCase());

    const filtered = this.products.filter(p => {
      const nameMatch = (p.name || '').toLowerCase().includes(search);
      const descMatch = (p.description || '').toLowerCase().includes(search);
      const searchMatch = nameMatch || descMatch;

      const productCategory = (p.category || 'uncategorized').toLowerCase();
      const categoryMatch = category === 'all' || productCategory === category;

      const productTags = Array.isArray(p.tags) ? p.tags.map(t => t.toLowerCase()) : [];

      // single-select behavior: activeTags is [] or [tag]
      // but use some() to keep it flexible (works if you later allow multiselect)
      const tagMatch = activeTags.length === 0 || activeTags.some(t => productTags.includes(t));

      return searchMatch && categoryMatch && tagMatch;
    });

    this.render(filtered);
  }

  // SINGLE-SELECT toggleTag: clicking a tag deactivates others; clicking same tag toggles it off
  toggleTag(btn) {
    // read tag from data attribute or fallback to button text
    const tag = (btn.dataset && btn.dataset.tag) ? String(btn.dataset.tag).toLowerCase() : btn.textContent.trim().toLowerCase();

    // deactivate all other tag buttons
    document.querySelectorAll('.tags button').forEach(b => {
      if (b !== btn) b.classList.remove('active');
    });

    // toggle clicked button (this allows clicking again to clear)
    btn.classList.toggle('active');

    // set activeTags to either [] or [tag]
    this.activeTags = btn.classList.contains('active') ? [tag] : [];

    // re-apply filters
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
    // Instead of redirecting to checkout-address.html
    window.location.href = 'my-cart.php';
  }

  goToCart() {
    // Navigate to the full cart page (my-orders.html)
    window.location.href = 'my-cart.php';
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
function logout() {cartInstance.logout();}
window.logout = logout;
function goToCart() { return cartInstance.goToCart(); }

// Make closeSizeModal globally accessible
function closeSizeModal() {
    cartInstance.closeSizeModal();
}
window.closeSizeModal = closeSizeModal;


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
window.goToCart = goToCart;

// Initialize the application
cartInstance.populateCategories();
cartInstance.fetchProducts();
cartInstance.fetchCart();