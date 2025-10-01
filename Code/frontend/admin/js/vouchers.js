export async function initVouchers() {
  const tableBody = document.querySelector("#voucher-table tbody");
  const addForm = document.getElementById("add-voucher-form");

  const modal = document.getElementById("edit-voucher-modal");
  const closeModal = document.getElementById("close-modal");
  const editForm = document.getElementById("edit-voucher-form");

  // Edit modal fields
  const editCode = document.getElementById("edit-code");
  const editType = document.getElementById("edit-type");
  const editDiscountType = document.getElementById("edit-discount-type");
  const editDiscountValue = document.getElementById("edit-discount-value");
  const editUsageLimit = document.getElementById("edit-usage-limit");
  const editStartDate = document.getElementById("edit-start-date");
  const editEndDate = document.getElementById("edit-end-date");
  const editIsActive = document.getElementById("edit-is-active");

  const customerModal = document.getElementById("customer-modal");
  const closeCustomerModal = document.getElementById("close-customer-modal");
  const customerTableBody = document.querySelector("#customer-table tbody");

  let currentVoucherCode = null;

  // ---------------------------
  // Load customers
async function loadCustomers() {
  try {
    const res = await fetch(apiUrl("customer")); // ✅ route to your PHP above
    const data = await res.json();

    // Case 1: array of customers
    if (Array.isArray(data)) {
      customerTableBody.innerHTML = data
        .map(
          (c) => `
          <tr>
            <td>${c.customer_id}</td>
            <td>${c.first_name} ${c.last_name}</td>
            <td>${c.email ?? "-"}</td>
            <td><button data-id="${c.customer_id}">Send</button></td>
          </tr>
        `
        )
        .join("");
      return;
    }

    // Case 2: error object from backend
    if (data && data.status === "error") {
      throw new Error(data.message || "Backend error");
    }

    // Case 3: unexpected response
    throw new Error("Unexpected response from server");
  } catch (err) {
    console.error("Failed to load customers:", err);
    customerTableBody.innerHTML =
      `<tr><td colspan="4">Failed to load customers: ${err.message}</td></tr>`;
  }
}


  // Open modal to send voucher
  window.openSendVoucherModal = async (voucher_code) => {
    currentVoucherCode = voucher_code;
    await loadCustomers();
    customerModal.style.display = "flex";
  };

  // Handle customer selection
  customerTableBody.addEventListener("click", async (e) => {
    const btn = e.target.closest("button[data-id]");
    if (!btn) return;
    const customer_id = btn.dataset.id;

    try {
      const res = await fetch(apiUrl("sendVoucher"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ customer_id, voucher_code: currentVoucherCode }),
      });
      const result = await res.json();
      alert(result.message);
      customerModal.style.display = "none";
    } catch (err) {
      console.error("Failed to send voucher:", err);
      alert("Something went wrong while sending the voucher.");
    }
  });

  // Close customer modal
  closeCustomerModal.onclick = () => (customerModal.style.display = "none");
  window.addEventListener("click", (e) => {
    if (e.target === customerModal) customerModal.style.display = "none";
    if (e.target === modal) modal.style.display = "none";
  });

  // ---------------------------
  // Load vouchers
  async function loadVouchers() {
    try {
      const res = await fetch(apiUrl("voucherList"));
      const result = await res.json();
      if (result.status !== "success") throw new Error(result.message);

      tableBody.innerHTML = result.vouchers
        .map(
          (v) => `
        <tr>
          <td>${v.voucher_id}</td>
          <td>${v.code}</td>
          <td>${v.type}</td>
          <td>${v.discount_type ? v.discount_value + (v.discount_type === "percent" ? "%" : "₱") : "-"}</td>
          <td>${v.used_count}/${v.usage_limit || "∞"}</td>
          <td>${v.is_active ? "Yes" : "No"}</td>
          <td>
            <button onclick="editVoucher(${v.voucher_id})">Edit</button>
            <button onclick="deleteVoucher(${v.voucher_id})" style="background:red; color:white;">Delete</button>
            <button onclick="openSendVoucherModal('${v.code}')" style="background:green; color:white;">Send</button>
          </td>
        </tr>
      `
        )
        .join("");
    } catch (err) {
      console.error("Failed to load vouchers:", err);
    }
  }

  // ---------------------------
  // Add voucher
  addForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(addForm).entries());

    try {
      const res = await fetch(apiUrl("voucherAdd"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });
      const result = await res.json();
      alert(result.message);
      if (result.status === "success") {
        addForm.reset();
        loadVouchers();
      }
    } catch (err) {
      console.error(err);
    }
  });

  // ---------------------------
  // Edit voucher
  window.editVoucher = async (id) => {
    try {
      const res = await fetch(apiUrl("voucherList"));
      const result = await res.json();
      if (result.status !== "success") return alert("Failed to get voucher data");

      const voucher = result.vouchers.find((v) => v.voucher_id == id);
      if (!voucher) return alert("Voucher not found");

      editForm.voucher_id.value = voucher.voucher_id;
      editCode.value = voucher.code;
      editType.value = voucher.type;
      editDiscountType.value = voucher.discount_type || "";
      editDiscountValue.value = voucher.discount_value || "";
      editUsageLimit.value = voucher.usage_limit || "";
      editStartDate.value = (voucher.start_date && voucher.start_date !== "0000-00-00") 
  ? voucher.start_date 
  : "";
      editEndDate.value = (voucher.end_date && voucher.end_date !== "0000-00-00") 
  ? voucher.end_date 
  : "";
      editIsActive.checked = !!voucher.is_active;

      modal.style.display = "flex";
    } catch (err) {
      console.error("Edit voucher failed:", err);
    }
  };

  closeModal.onclick = () => (modal.style.display = "none");

  // Save edits
  editForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(editForm).entries());
    formData.is_active = editIsActive.checked ? 1 : 0;

    try {
      const res = await fetch(apiUrl("voucherUpdate"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });
      const result = await res.json();
      alert(result.message);
      if (result.status === "success") {
        modal.style.display = "none";
        loadVouchers();
      }
    } catch (err) {
      console.error(err);
    }
  });

  // ---------------------------
  // Delete voucher
  window.deleteVoucher = async (id) => {
    if (!confirm("Are you sure you want to delete this voucher?")) return;

    try {
      const res = await fetch(apiUrl("voucherDelete"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ voucher_id: id }),
      });
      const result = await res.json();
      alert(result.message);
      loadVouchers();
    } catch (err) {
      console.error(err);
    }
  };

  // ---------------------------
  // Discount type toggle
  editType.addEventListener("change", () => {
    const disable = editType.value === "free_shipping";
    editDiscountType.disabled = disable;
    editDiscountValue.disabled = disable;
    if (disable) {
      editDiscountType.value = "";
      editDiscountValue.value = "";
    }
  });

  // Initial load
  await loadVouchers();
}
