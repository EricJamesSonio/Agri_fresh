async function loadProfile() {
  const res = await fetch(apiUrl("profile?action=getProfile"), {
    credentials: "include"
  });
  const data = await res.json();

  if (data.status === "success") {
    const u = data.user;
    document.getElementById("role").value = u.role;
    document.getElementById("id").value = u.id;
    document.getElementById("first_name").value = u.first_name;
    document.getElementById("last_name").value = u.last_name;
    document.getElementById("email").value = u.email;

    if (u.role === "customer") {
      document.getElementById("contactWrapper").classList.remove("hidden");
      document.getElementById("contact").value = u.contact;
      document.getElementById("addresses").classList.remove("hidden");
      loadAddresses();
    }
  } else {
    alert(data.message || "Failed to load profile.");
  }
}

// Update profile
document.getElementById("profileForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = Object.fromEntries(new FormData(e.target).entries());

  const res = await fetch(apiUrl("profile?action=updateProfile"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(formData),
    credentials: "include"
  });
  const result = await res.json();
  alert(result.message);
  if (result.status === "success") {
    loadProfile();
  }
});

// Load addresses
async function loadAddresses() {
  const res = await fetch(apiUrl("profile?action=getAddresses"), {
    credentials: "include"
  });
  const data = await res.json();
  if (data.status === "success") {
    const container = document.getElementById("address-list");
    container.innerHTML = "";
    data.addresses.forEach(addr => {
      const div = document.createElement("div");
      div.className = "address-card";
      div.textContent = addr.full_address;
      container.appendChild(div);
    });
  }
}

// Show/hide add address form
const addAddressBtn = document.getElementById("addAddressBtn");
const addressFormWrapper = document.getElementById("add-address-form");
const addressForm = document.getElementById("addressForm");

addAddressBtn.addEventListener("click", () => {
  addressFormWrapper.classList.add("show");
  addAddressBtn.style.display = "none";
});

function toggleAddressForm() {
  addressFormWrapper.classList.remove("show");
  addAddressBtn.style.display = "inline-block";
}

// Handle add address form
addressForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = Object.fromEntries(new FormData(e.target).entries());

  const res = await fetch(apiUrl("profile?action=addAddress"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(formData),
    credentials: "include"
  });
  const result = await res.json();

  const msgBox = document.getElementById("address-message");
  msgBox.textContent = result.message;
  msgBox.className = result.status === "success" ? "success" : "error";

  if (result.status === "success") {
    addressForm.reset();
    toggleAddressForm();
    loadAddresses();
  }
});

loadProfile();
