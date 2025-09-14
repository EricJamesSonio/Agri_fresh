let selectedAddressId = null;
let cartData = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
  if (!CONFIG.CUSTOMER_ID) {
    alert('Please login to continue');
    window.location.href = 'login.php';
    return;
  }

  loadOrderSummary();
  loadAddresses();
});

async function loadOrderSummary() {
  try {
    const response = await fetch(apiUrl(`cart?customer_id=${CONFIG.CUSTOMER_ID}`));
    const data = await response.json();
    cartData = data || [];

    const itemsHtml = (Array.isArray(cartData) && cartData.length > 0)
      ? cartData.map(item => {
          const total = (item.price_each || 0) * (item.quantity || 0);
          return `<div>${escapeHtml(item.name)} × ${item.quantity} — ${total.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</div>`;
        }).join('')
      : '<div>No items in cart.</div>';

    let subtotal = cartData.reduce((sum, item) => sum + (item.price_each || 0) * (item.quantity || 0), 0);
    let shippingFee = 50;
    let discountAmount = 0;
    let voucherCode = null;
    let voucherType = null;

    const raw = localStorage.getItem("applied_voucher");
    if (raw) {
      try {
        const v = JSON.parse(raw);
        voucherCode = v.code || null;
        voucherType = v.type || null;

        if (v.type === "discount") {
          if (v.discount_type === "percent") {
            discountAmount = subtotal * (v.discount_value / 100);
          } else if (v.discount_type === "fixed") {
            discountAmount = v.discount_value;
          }
        } else if (v.type === "free_shipping") {
          shippingFee = 0;
        }
      } catch (e) {
        console.warn("Invalid voucher in localStorage", e);
      }
    }

    let totalAmount = subtotal + shippingFee - discountAmount;
    if (totalAmount < 0) totalAmount = 0;

    const subtotalFmt = subtotal.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
    const shippingFmt = shippingFee > 0 
      ? shippingFee.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) 
      : "Free";
    const discountFmt = discountAmount > 0 
      ? `- ${discountAmount.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}`
      : "₱0.00";
    const totalFmt = totalAmount.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });

    const orderSummaryEl = document.querySelector('.order-summary');
    orderSummaryEl.innerHTML = `
      <h3>Order Summary</h3>
      <div id="order-items">${itemsHtml}</div>
      <div style="margin-top:1rem; line-height:1.6;">
        <div>Subtotal: <strong>${subtotalFmt}</strong></div>
        <div>Shipping: <strong>${shippingFmt}</strong></div>
        <div>Voucher: ${voucherCode 
          ? `<strong>${escapeHtml(voucherCode)}</strong> <span style="color:#2e7d32">(${voucherType})</span> — <strong>${discountFmt}</strong>` 
          : '<em>None</em>'}
        </div>
        <div style="font-weight: bold; margin-top: 0.5rem;">
          Payable: <strong id="order-total">${totalFmt}</strong>
        </div>
      </div>
    `;
  } catch (error) {
    console.error("Failed to load order summary:", error);
    const orderSummaryEl = document.querySelector('.order-summary');
    orderSummaryEl.innerHTML = `
      <h3>Order Summary</h3>
      <div id="order-items"><p class="error">Failed to load order summary.</p></div>
      <div style="font-weight:bold; margin-top:1rem;">Total: <span id="order-total">₱0.00</span></div>
    `;
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

async function loadAddresses() {
  try {
    const response = await fetch(apiUrl(`address?customer_id=${CONFIG.CUSTOMER_ID}`));
    const result = await response.json();
    
    if (result.status === 'success') {
      displayAddresses(result.data);
    } else {
      document.getElementById('address-list').innerHTML = 
        '<p>No addresses found. Please add a delivery address.</p>';
    }
  } catch (error) {
    console.error('Failed to load addresses:', error);
    document.getElementById('address-list').innerHTML = 
      '<p class="error">Failed to load addresses.</p>';
  }
}

function displayAddresses(addresses) {
  const container = document.getElementById('address-list');
  
  if (addresses.length === 0) {
    container.innerHTML = '<p>No addresses found. Please add a delivery address.</p>';
    return;
  }

  container.innerHTML = addresses.map(addr => `
    <div class="address-card" onclick="selectAddress(${addr.address_id})" data-address-id="${addr.address_id}">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
          <strong>${addr.street}</strong>
          ${addr.is_default ? '<span class="default-badge">Default</span>' : ''}
        </div>
        <input type="radio" name="selected_address" value="${addr.address_id}" ${addr.is_default ? 'checked' : ''}>
      </div>
      <div>${addr.city}${addr.state ? ', ' + addr.state : ''} ${addr.postal_code || ''}</div>
      <div>${addr.country}</div>
    </div>
  `).join('');

  const defaultAddress = addresses.find(addr => addr.is_default);
  if (defaultAddress) {
    selectedAddressId = defaultAddress.address_id;
    document.getElementById('place-order-btn').disabled = false;
  }
}

function selectAddress(addressId) {
  document.querySelectorAll('.address-card').forEach(card => {
    card.classList.remove('selected');
  });
  
  const selectedCard = document.querySelector(`[data-address-id="${addressId}"]`);
  selectedCard.classList.add('selected');
  
  document.querySelector(`input[value="${addressId}"]`).checked = true;
  
  selectedAddressId = addressId;
  document.getElementById('place-order-btn').disabled = false;
}

function toggleAddressForm() {
  const form = document.getElementById('add-address-form');
  form.classList.toggle('show');
  
  if (form.classList.contains('show')) {
    document.getElementById('street').focus();
  }
}

document.getElementById('address-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const addressData = {
    customer_id: CONFIG.CUSTOMER_ID,
    action: 'add',
    street: formData.get('street'),
    city: formData.get('city'),
    state: formData.get('state'),
    postal_code: formData.get('postal_code'),
    country: formData.get('country'),
    is_default: formData.get('is_default') ? true : false
  };

  try {
    const response = await fetch(apiUrl('address'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(addressData)
    });

    const result = await response.json();
    
    if (result.status === 'success') {
      document.getElementById('address-message').innerHTML = 
        '<div class="success">Address added successfully!</div>';
      
      e.target.reset();
      setTimeout(() => {
        toggleAddressForm();
        loadAddresses();
      }, 1000);
    } else {
      document.getElementById('address-message').innerHTML = 
        `<div class="error">${result.message}</div>`;
    }
  } catch (error) {
    console.error('Failed to add address:', error);
    document.getElementById('address-message').innerHTML = 
      '<div class="error">Failed to add address. Please try again.</div>';
  }
});

async function placeOrder() {
  if (!selectedAddressId) {
    alert("Please select a delivery address.");
    return;
  }

  const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

  let voucherCode = null;
  const raw = localStorage.getItem("applied_voucher");
  if (raw) {
    try {
      const v = JSON.parse(raw);
      voucherCode = v.code || null;
    } catch (e) {
      console.warn("Invalid voucher in storage");
    }
  }

  const payload = {
    customer_id: CONFIG.CUSTOMER_ID,
    address_id: selectedAddressId,
    payment_method: paymentMethod,
    voucher_code: voucherCode
  };

  try {
    const response = await fetch(apiUrl("orders"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    if (result.status === "success") {
      alert("Order placed successfully!");
      localStorage.removeItem("applied_voucher");
      window.location.href = `order-confirmation.php?order_id=${result.order_id}`;
    } else {
      alert("Error: " + result.message);
    }
  } catch (err) {
    console.error("Place order failed:", err);
    alert("Failed to place order. Try again.");
  }
}

function goBack() {
  window.location.href = 'index.php';
}
