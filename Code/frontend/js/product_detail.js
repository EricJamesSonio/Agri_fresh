const user = JSON.parse(localStorage.getItem('currentUser'));
if (!user) { 
  alert('Please log in first'); 
  location.href = 'login.php'; 
}

const allOrders = JSON.parse(localStorage.getItem('orders') || '[]');
let orders = allOrders.filter(o => o.userId === user.id);

/* ---------- render table ---------- */
function renderTable() {
  const tbody = document.getElementById('orderBody');
  const empty = document.getElementById('emptyState');
  if (!orders.length) { empty.style.display = 'block'; return; }
  empty.style.display = 'none';

  let rows = '';
  orders.forEach(o => {
    o.items.forEach(i => {
      const lineTotal = i.price * i.qty;
      rows += `
        <tr data-order="${o.id}" data-product="${i.name}" data-price="${i.price}">
          <td><input type="checkbox" class="rowCheck" onchange="updateControls()"></td>
          <td><img src="${i.img || '/images/no-img.jpg'}" alt="${i.name}"></td>
          <td>${i.name} <small>(${i.option})</small></td>
          <td>₱${i.price}</td>
          <td>
            <button class="qty-btn" onclick="changeQty(this,-1)">−</button>
            <span class="qtyVal">${i.qty}</span>
            <button class="qty-btn" onclick="changeQty(this,1)">+</button>
          </td>
          <td class="lineTotal">₱${lineTotal}</td>
          <td><span class="delete-btn" onclick="deleteItem(${o.id},'${i.name}')">×</span></td>
        </tr>`;
    });
  });
  tbody.innerHTML = rows;
}

/* ---------- quantity change ---------- */
function changeQty(btn, delta) {
  const tr   = btn.closest('tr');
  const span = tr.querySelector('.qtyVal');
  let qty    = parseInt(span.textContent, 10) + delta;
  if (qty < 1) qty = 1;
  span.textContent = qty;

  const price = parseFloat(tr.dataset.price);
  tr.querySelector('.lineTotal').textContent = '₱' + (price * qty);
  updateControls();
}

/* ---------- select-all / bulk ---------- */
function toggleAll(master) {
  document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = master.checked);
  updateControls();
}

function updateControls() {
  const checked = [...document.querySelectorAll('.rowCheck:checked')];

  // top bar bulk delete
  const bulkBtn   = document.getElementById('bulkDeleteBtn');
  bulkBtn.style.display = checked.length ? 'inline-block' : 'none';

  // bottom summary
  let subTotal = 0;
  checked.forEach(cb => {
    const tr = cb.closest('tr');
    subTotal += parseFloat(tr.querySelector('.lineTotal').textContent.replace('₱', ''));
  });

  document.getElementById('itemCount').textContent = checked.length;
  document.getElementById('grandTotal').textContent = '₱' + subTotal;
  recalcGrandTotal();
}



function recalcGrandTotal() {
  const checked = [...document.querySelectorAll('.rowCheck:checked')];
  let subTotal = 0;
  checked.forEach(cb => {
    const tr = cb.closest('tr');
    subTotal += parseFloat(tr.querySelector('.lineTotal').textContent.replace('₱', ''));
  });

  const coins = Math.min(parseInt(document.getElementById('coins').value || 0, 10), subTotal - voucherValue);
  document.getElementById('coins').value = coins;
  document.getElementById('coinsDiscount').textContent = '-₱' + coins;

  const final = subTotal - voucherValue - coins;
  document.getElementById('finalTotal').textContent = '₱' + final;
}

/* ---------- delete ---------- */
function deleteItem(orderId, productName) {
  let ordersArr = JSON.parse(localStorage.getItem('orders') || '[]');
  const order = ordersArr.find(o => o.id === orderId);
  if (!order) return;

  order.items = order.items.filter(i => i.name !== productName);
  order.total = order.items.reduce((s, i) => s + i.price * i.qty, 0);

  if (!order.items.length) {
    ordersArr = ordersArr.filter(o => o.id !== orderId);
  }
  localStorage.setItem('orders', JSON.stringify(ordersArr));
  orders = ordersArr.filter(o => o.userId === user.id);
  renderTable();
  updateControls();
}

/* ---------- checkout ---------- */
function checkout() {
  const checked = [...document.querySelectorAll('.rowCheck:checked')];
  if (!checked.length) { alert('Select at least one item to check out'); return; }
  alert('Proceeding to payment gateway…');
  // redirect / clear cart etc. here
}

renderTable();
