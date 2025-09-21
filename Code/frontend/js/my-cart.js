/* === Helper to handle images consistently === */
ShoppingCart.prototype.getImageSrc = function(img) {
  if (!img) return '../images/placeholder.jpg';
  return img.trim().toLowerCase().startsWith('http') ? img : imageUrl(img);
};

/* === Checkout function for selected items === */
function checkout() {
  if (cartInstance.cart.length === 0) {
    alert('Your cart is empty!');
    return;
  }

  const selectedItems = [];
  const checkboxes = document.querySelectorAll('.selectItem:checked');

  if (checkboxes.length === 0) {
    selectedItems.push(...cartInstance.cart); // checkout all
  } else {
    checkboxes.forEach(cb => {
      const idx = parseInt(cb.dataset.idx, 10);
      selectedItems.push(cartInstance.cart[idx]);
    });
  }

  if (selectedItems.length === 0) {
    alert('Please select items to checkout!');
    return;
  }

  localStorage.setItem('checkout_items', JSON.stringify(selectedItems));
  window.location.href = 'checkout-address.php';
}
/* === Toggle all checkboxes === */
function toggleAll(checkbox) {
  const itemCheckboxes = document.querySelectorAll('.selectItem');
  itemCheckboxes.forEach(cb => cb.checked = checkbox.checked);
  updateCheckoutButton();
}

/* === Update checkout button label === */
function updateCheckoutButton() {
  const checkoutBtn = document.querySelector('.checkout-btn');
  const selectedCount = document.querySelectorAll('.selectItem:checked').length;
  const totalItems = document.querySelectorAll('.selectItem').length;

  if (selectedCount === 0) {
    checkoutBtn.textContent = `Check Out All (${totalItems})`;
  } else {
    checkoutBtn.textContent = `Check Out (${selectedCount})`;
  }
}

/* === Render Cart Table === */
function renderOrderTable() {
  const tbody = document.getElementById('orderBody');
  const grandTotalEl = document.getElementById('grandTotal');
  const finalTotalEl = document.getElementById('finalTotal');
  const itemCountEl = document.getElementById('itemCount');

  if (!tbody) return;

  if (cartInstance.cart.length === 0) {
    document.getElementById('emptyState').style.display = 'block';
    document.querySelector('.order-table').style.display = 'none';
    document.querySelector('.cart-footer').style.display = 'none';
    return;
  } else {
    document.getElementById('emptyState').style.display = 'none';
    document.querySelector('.order-table').style.display = 'table';
    document.querySelector('.cart-footer').style.display = 'block';
  }

  tbody.innerHTML = cartInstance.cart.map((item, idx) => {
    const qty = parseFloat(item.qty) || 0;
    const price = parseFloat(item.price) || 0;
    const totalPrice = price * qty;
    const imgSrc = cartInstance.getImageSrc(item.img);

    return `
      <tr>
        <td><input type="checkbox" class="selectItem" data-idx="${idx}" onchange="updateCheckoutButton()"></td>
        <td><img src="${imgSrc}" alt="${item.name}" width="50"></td>
        <td>${item.name}</td>
        <td>${price.toLocaleString('en-PH',{style:'currency',currency:'PHP'})}</td>
        <td>
          <button class="qty-btn" onclick="cartInstance.changeQty(${idx}, -1)">-</button>
          ${qty}
          <button class="qty-btn" onclick="cartInstance.changeQty(${idx}, 1)">+</button>
        </td>
        <td>${totalPrice.toLocaleString('en-PH',{style:'currency',currency:'PHP'})}</td>
        <td><button class="delete-btn" onclick="cartInstance.removeItem(${idx})">Remove</button></td>
      </tr>
    `;
  }).join('');

  const grandTotal = cartInstance.cart.reduce((sum, i) => sum + (parseFloat(i.price)||0)*(parseFloat(i.qty)||0), 0);

  grandTotalEl.textContent = grandTotal.toLocaleString('en-PH',{style:'currency',currency:'PHP'});
  finalTotalEl.textContent = grandTotal.toLocaleString('en-PH',{style:'currency',currency:'PHP'});
  itemCountEl.textContent = cartInstance.cart.reduce((c, i) => c + (parseFloat(i.qty)||0), 0);

  updateCheckoutButton();
}

/* === Apply Voucher === */
async function applyVoucher() {
  const code = document.getElementById("voucherCode").value.trim();
  if (!code) return alert("Please enter a voucher code.");

  try {
    const res = await fetch(apiUrl("voucher"), {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify({
        voucher_code: code,
        customer_id: localStorage.getItem("customer_id")
      })
    });

    const result = await res.json();
    if (result.status === "success") {
      const v = result.voucher || {};
      const grandTotal = cartInstance.cart.reduce((s,i)=>s+(parseFloat(i.price)||0)*(parseFloat(i.qty)||0),0);

      let discountAmount = 0;
      if (v.type === "discount") {
        if (v.discount_type === "percent") discountAmount = grandTotal * (parseFloat(v.discount_value)||0)/100;
        if (v.discount_type === "fixed") discountAmount = parseFloat(v.discount_value)||0;
      } else if (v.type === "free_shipping") {
        discountAmount = 50; // example
      }

      discountAmount = Math.min(Math.max(discountAmount,0),grandTotal);
      const finalTotal = grandTotal - discountAmount;

      document.getElementById("voucherDiscount").textContent = 
        `- ${discountAmount.toLocaleString("en-PH",{style:"currency",currency:"PHP"})}`;
      document.getElementById("finalTotal").textContent = 
        finalTotal.toLocaleString("en-PH",{style:"currency",currency:"PHP"});

      localStorage.setItem("applied_voucher", JSON.stringify({
        code:v.code||code,
        type:v.type,
        discount_type:v.discount_type,
        discount_value:parseFloat(v.discount_value||0),
        discount_amount:discountAmount,
        final_total:finalTotal
      }));

      alert("Voucher applied successfully!");
    } else {
      alert(result.message || "Invalid voucher.");
    }
  } catch (err) {
    console.error("Voucher apply failed:", err);
    alert("Something went wrong while applying voucher.");
  }
}

/* === Override cart update to re-render table automatically === */
const originalUpdateCart = cartInstance.updateCart.bind(cartInstance);
cartInstance.updateCart = function() {
  originalUpdateCart();
  renderOrderTable();
};

/* === Initialize cart table on page load === */
cartInstance.fetchCart();
