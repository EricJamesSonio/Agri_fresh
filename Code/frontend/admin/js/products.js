export async function initPage() {
  let categories = [];
  let originalProduct = null; // Store original product data when editing

  const form = document.getElementById("product-form");
  const formTitle = document.getElementById("form-title");
  const addNewBtn = document.getElementById("add-new-product");

  // -------------------------
  // Load categories
  // -------------------------
  async function loadCategories() {
    try {
      const res = await fetch(apiUrl("category"));
      categories = await res.json();
      const select = document.getElementById("product-category");
      if (!select) return;
      select.innerHTML = categories
        .map(c => `<option value="${c.id}">${c.name}</option>`)
        .join("");
    } catch {
      categories = [];
    }
  }

  // -------------------------
  // Load products
  // -------------------------
  async function loadProducts() {
    try {
      const res = await fetch(apiUrl("products"));
      const products = await res.json();
      const tbody = document.querySelector("#products-table tbody");
      if (!tbody) return;

      tbody.innerHTML = products
        .map(
          p => `
        <tr ${p.stock_quantity === 0 ? 'style="opacity:0.5;"' : ""}>
          <td>${p.id}</td>
          <td>${p.name}</td>
          <td>₱${p.price.toFixed(2)}</td>
          <td>${p.stock_quantity === 0 ? "Out of Stock" : p.stock_quantity}</td>
          <td>${p.category || "Uncategorized"}</td>
          <td>${p.size_value ? p.size_value + " " + (p.size_unit || "") : "-"}</td>
          <td>${p.tags?.join(", ") || ""}</td>
          <td>
            <button type="button" onclick="editProduct(${p.id})">Edit</button>
            <button type="button" onclick="deleteProduct(${p.id})" 
              style="margin-left:5px; background-color:red; color:white; border:none; padding:5px 10px; border-radius:4px;">
              Remove
            </button>
          </td>
        </tr>
      `
        )
        .join("");
    } catch {
      console.error("Failed to load products.");
    }
  }

  // -------------------------
  // Delete product
  // -------------------------
  async function deleteProduct(id) {
    try {
      const res = await fetch(apiUrl("products") + `&id=${id}`);
      const product = await res.json();
      if (!product) return alert("Product not found.");

      if (
        !confirm(
          `Remove all items with name "${product.name}" (including all sizes)?`
        )
      )
        return;

      const delRes = await fetch(
        apiUrl("products") + `&name=${encodeURIComponent(product.name)}`,
        {
          method: "DELETE",
        }
      );
      const result = await delRes.json();
      alert(result.message);
      await loadProducts();
    } catch {
      alert("Failed to delete product(s).");
    }
  }
  window.deleteProduct = deleteProduct;

  // -------------------------
  // Edit product
  // -------------------------
  async function editProduct(id) {
    try {
      const res = await fetch(apiUrl("products") + `&id=${id}`);
      const product = await res.json();
      if (!product) return;

      originalProduct = { ...product };
      if (formTitle) formTitle.textContent = "Update Product";

      if (form) {
        form.querySelector("#product-id").value = product.id;
        form.querySelector("#product-name").value = product.name;
        form.querySelector("#product-price").value = product.price;
        form.querySelector("#product-stock").value = product.stock_quantity || 0;
        form.querySelector("#product-image").value = product.img || "";
        form.querySelector("#product-size-value").value = product.size_value || 0;
        form.querySelector("#product-size-unit").value = product.size_unit || "";
        const select = form.querySelector("#product-category");
        if (select && categories.length > 0)
          select.value = product.category_id || "";
        form.querySelector("#product-organic").checked =
          product.tags.includes("organic");
        form.querySelector("#product-seasonal").checked =
          product.tags.includes("seasonal");
      }
    } catch {}
  }
  window.editProduct = editProduct;

  // -------------------------
  // Live validation
  // -------------------------
  function setupLiveValidation() {
    if (!form) return; // skip if no form

    const inputs = [
      {
        el: form.querySelector("#product-name"),
        validator: val => {
          if (!val) return "Product name cannot be empty.";
          if (!/^[A-Za-z\s]+$/.test(val))
            return "Only letters and spaces allowed.";
          return "";
        },
      },
      {
        el: form.querySelector("#product-image"),
        validator: val => {
          const isEditing = form.querySelector("#product-id").value;
          if (
            val &&
            !/\.(jpg|jpeg|png|gif|webp)$/i.test(val)
          )
            return "Invalid image URL (.jpg, .png, .gif, .webp).";
          if (!isEditing && !val)
            return "Image URL is required for new products.";
          return "";
        },
      },
      {
        el: form.querySelector("#product-stock"),
        validator: val => {
          if (parseInt(val) < 0) return "Stock cannot be negative.";
          return "";
        },
      },
    ];

    inputs.forEach(({ el, validator }) => {
      if (!el) return;
      let errorElem = el.parentNode.querySelector(".error-text");
      if (!errorElem) {
        errorElem = document.createElement("div");
        errorElem.className = "error-text";
        errorElem.style.color = "red";
        errorElem.style.fontSize = "0.85em";
        errorElem.style.position = "absolute";
        errorElem.style.top = "-18px";
        errorElem.style.left = "0";
        el.parentNode.style.position = "relative";
        el.parentNode.insertBefore(errorElem, el);
      }
      let timeout;
      el.addEventListener("input", () => {
        const msg = validator(el.value.trim());
        if (msg) {
          errorElem.textContent = msg;
          el.setCustomValidity("invalid");
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            errorElem.textContent = "";
            el.setCustomValidity("");
          }, 3000);
        } else {
          errorElem.textContent = "";
          el.setCustomValidity("");
        }
      });
    });
  }

  // -------------------------
  // Form submit
  // -------------------------
  if (form) {
    setupLiveValidation();
    form.addEventListener("submit", async e => {
      e.preventDefault();

      const id = form.querySelector("#product-id").value;
      const name = form.querySelector("#product-name").value.trim();
      const price = parseFloat(form.querySelector("#product-price").value);
      const stock_quantity =
        parseInt(form.querySelector("#product-stock").value) || 0;
      const image_url = form.querySelector("#product-image").value.trim();
      const category =
        parseInt(form.querySelector("#product-category").value) || null;
      const size_value =
        parseFloat(form.querySelector("#product-size-value").value) || 0;
      const size_unit = form
        .querySelector("#product-size-unit")
        .value.trim();
      const is_organic = form.querySelector("#product-organic").checked ? 1 : 0;
      const is_seasonal = form.querySelector("#product-seasonal").checked
        ? 1
        : 0;

      if (!name || !/^[A-Za-z\s]+$/.test(name) || stock_quantity < 0) return;
      if (!id && (!image_url || !/\.(jpg|jpeg|png|gif|webp)$/i.test(image_url))) {
        alert(
          "Please enter a valid image URL for new product (.jpg, .png, .gif, .webp)."
        );
        return;
      }
      if (!size_unit) {
        alert("Please select a size unit.");
        return;
      }

      // -----------------------
      // Build payload
      // -----------------------
      let payload;
      if (!id) {
        payload = {
          id: null,
          name,
          description: "",
          price,
          stock_quantity,
          image_url,
          category,
          size_value,
          size_unit,
          is_organic,
          is_seasonal,
        };
      } else {
        payload = {
          id,
          name,
          price,
          stock_quantity,
          image_url,
          category,
          size_value,
          size_unit,
          is_organic,
          is_seasonal,
        };
      }

      try {
        const response = await fetch(apiUrl("products"), {
          method: id ? "PUT" : "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });
        const result = await response.json();
        alert(result.message);
        form.reset();
        if (formTitle) formTitle.textContent = "Add New Product";
        originalProduct = null;
        await loadProducts();
      } catch {
        alert("Failed to save product.");
      }
    });
  }

  // -------------------------
  // Add New Product button
  // -------------------------
  if (addNewBtn && form) {
    addNewBtn.addEventListener("click", () => {
      originalProduct = null;
      form.reset();
      if (formTitle) formTitle.textContent = "Add New Product";
      form.querySelector("#product-id").value = "";
      form.querySelector("#product-name")?.focus();
    });
  }

  // -------------------------
  // Initialize page
  // -------------------------
  await loadCategories();
  await loadProducts();
}
