let products = []; 
let cart = [];
let activeTags = [];

async function fetchProducts() {
  try {
    const res = await fetch(apiUrl('products')); // <-- uses config.js
    if (!res.ok) throw new Error('Failed to fetch products');

    products = await res.json();
    products = products.map(p => ({
      ...p,
      img: `${CONFIG.IMAGE_PATH}/${p.img}` // use config.js IMAGE_PATH
    }));

    render(products);
  } catch (err) {
    console.error(err);
    document.getElementById('products').innerHTML = `<p style="color:red;">Failed to load products</p>`;
  }
}


// Render cards
function render(list = products) {
  const grid = document.getElementById('products');
  grid.innerHTML = list.map(p => `
    <div class="card">
      <img src="${p.img}" alt="${p.name}" loading="lazy">
      <div class="card-body">
        <h4>${p.name}</h4>
        <span class="price">₱${p.price}</span>
        <button onclick="addToCart('${p.name}',${p.price})">Add to Cart</button>
      </div>
    </div>
  `).join('');
}

// Cart helpers
function addToCart(name, price) {
  const found = cart.find(i => i.name === name);
  found ? found.qty++ : cart.push({ name, price, qty: 1 });
  updateCart();
}
function updateCart() {
  const list   = document.getElementById('cart-items');
  const total  = document.getElementById('cart-total');
  const count  = document.getElementById('cart-count');

  list.innerHTML = cart.map(
    (item, idx) =>
      `<li>
         ${item.name} – ₱${item.price} × ${item.qty}
         <span class="item-controls">
           <button class="minus" onclick="changeQty(${idx},-1)">-</button>
           <button class="plus"  onclick="changeQty(${idx},1)">+</button>
           <button class="remove" onclick="removeItem(${idx})">×</button>
         </span>
       </li>`
  ).join('');

  const grand = cart.reduce((t, i) => t + i.price * i.qty, 0);
  total.textContent = '₱' + grand;
  count.textContent = cart.reduce((c, i) => c + i.qty, 0);

  document.getElementById('cart').classList.toggle('show', cart.length > 0);
}
function changeQty(idx, delta) {
  cart[idx].qty += delta;
  if (cart[idx].qty < 1) cart.splice(idx, 1);
  updateCart();
}
function removeItem(idx) {
  cart.splice(idx, 1);
  updateCart();
}
function closeCart() {
  document.getElementById('cart').classList.remove('show');
}

// Filters
function filterProducts() {
  const search = document.getElementById('search').value.toLowerCase();
  const category = document.getElementById('category').value;
  let filtered = products.filter(p => {
    const matchSearch = p.name.toLowerCase().includes(search);
    const matchCat = category === 'all' || p.category === category;
    const matchTag = activeTags.length === 0 || activeTags.every(t => p.tags.includes(t));
    return matchSearch && matchCat && matchTag;
  });
  render(filtered);
}
function toggleTag(btn, tag) {
  btn.classList.toggle('active');
  activeTags = [...document.querySelectorAll('.tags .active')].map(b => b.textContent.trim());
  filterProducts();
}

// Checkout
function checkout() {
  alert('Proceeding to payment (COD / Card coming soon)');
}

// Scroll
function scrollToProducts() {
  document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
}

// Init
fetchProducts(); // uses API now
