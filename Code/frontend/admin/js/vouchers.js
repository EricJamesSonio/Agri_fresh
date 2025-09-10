export async function initVouchers() {
  const tableBody = document.querySelector("#voucher-table tbody");
  const addForm = document.getElementById("add-voucher-form");

  const modal = document.getElementById("edit-voucher-modal");
  const closeModal = document.getElementById("close-modal");
  const editForm = document.getElementById("edit-voucher-form");

  const editCode = document.getElementById("edit-code");
  const editType = document.getElementById("edit-type");
  const editDiscountType = document.getElementById("edit-discount-type");
  const editDiscountValue = document.getElementById("edit-discount-value");
  const editUsageLimit = document.getElementById("edit-usage-limit");
  const editStartDate = document.getElementById("edit-start-date");
  const editEndDate = document.getElementById("edit-end-date");
  const editIsActive = document.getElementById("edit-is-active");

  // Load all vouchers
  async function loadVouchers() {
    try {
      const res = await fetch(apiUrl('voucherList'));
      const result = await res.json();
      if (result.status !== "success") throw new Error(result.message);

      tableBody.innerHTML = result.vouchers.map(v => `
        <tr>
          <td>${v.voucher_id}</td>
          <td>${v.code}</td>
          <td>${v.type}</td>
          <td>${v.discount_type ? v.discount_value + (v.discount_type === 'percent' ? '%' : '₱') : '-'}</td>
          <td>${v.used_count}/${v.usage_limit || '∞'}</td>
          <td>${v.is_active ? 'Yes' : 'No'}</td>
          <td>
            <button onclick="editVoucher(${v.voucher_id})">Edit</button>
            <button onclick="deleteVoucher(${v.voucher_id})" style="background:red; color:white;">Delete</button>
          </td>
        </tr>
      `).join('');

    } catch (err) {
      console.error("Failed to load vouchers:", err);
    }
  }

  // Add voucher
  addForm.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(addForm).entries());

    try {
      const res = await fetch(apiUrl('voucherAdd'), {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify(formData)
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

  // Edit voucher
  window.editVoucher = async (id) => {
    // Fetch voucher data
    const res = await fetch(apiUrl('voucherList'));
    const result = await res.json();
    if (result.status !== "success") return alert("Failed to get voucher data");

    const voucher = result.vouchers.find(v => v.voucher_id == id);
    if (!voucher) return alert("Voucher not found");

    // Pre-fill modal form
    editForm.voucher_id.value = voucher.voucher_id;
    editCode.value = voucher.code;
    editType.value = voucher.type;
    editDiscountType.value = voucher.discount_type || "";
    editDiscountValue.value = voucher.discount_value || "";
    editUsageLimit.value = voucher.usage_limit || "";
    editStartDate.value = voucher.start_date || "";
    editEndDate.value = voucher.end_date || "";
    editIsActive.checked = voucher.is_active ? true : false;

    // Show modal
    modal.style.display = "flex";
  };

  // Close modal
  closeModal.onclick = () => modal.style.display = "none";
  window.onclick = e => { if(e.target === modal) modal.style.display = "none"; };

  // Handle edit form submit
  editForm.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(editForm).entries());
    formData.is_active = editIsActive.checked ? 1 : 0;

    try {
      const res = await fetch(apiUrl('voucherUpdate'), {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify(formData)
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

  // Delete voucher
  window.deleteVoucher = async (id) => {
    if (!confirm("Are you sure you want to delete this voucher?")) return;

    try {
      const res = await fetch(apiUrl('voucherDelete'), {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({voucher_id: id})
      });
      const result = await res.json();
      alert(result.message);
      loadVouchers();
    } catch (err) {
      console.error(err);
    }
  };

  // Hide discount fields if type is free_shipping in edit modal
  editType.addEventListener("change", () => {
    if (editType.value === "free_shipping") {
      editDiscountType.disabled = true;
      editDiscountValue.disabled = true;
      editDiscountType.value = "";
      editDiscountValue.value = "";
    } else {
      editDiscountType.disabled = false;
      editDiscountValue.disabled = false;
    }
  });

  await loadVouchers();
}
