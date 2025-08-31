document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  
  const email = e.target.email.value;
  const password = e.target.password.value;
  
  try {
    console.log("Attempting to connect to:", apiUrl("login")); // Debug
    
    const res = await fetch(apiUrl("login"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password })
    });
    
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    
    const data = await res.json();
    console.log("Login response:", data); // 👀 Debug
    
    if (data.status === "success") {
      // ✅ FIXED: Clear any existing localStorage first
      localStorage.removeItem("customer_id");
      localStorage.removeItem("customer_name");
      localStorage.removeItem("role");
      
      // ✅ Store values individually and validate they're being stored correctly
      localStorage.setItem("customer_id", data.id.toString());
      localStorage.setItem("customer_name", data.name);
      localStorage.setItem("role", data.role);
      
      // ✅ Debug: Verify what was actually stored
      console.log("Stored customer_id:", localStorage.getItem("customer_id"));
      console.log("Stored customer_name:", localStorage.getItem("customer_name"));
      console.log("Stored role:", localStorage.getItem("role"));
      
      alert(`Welcome ${data.role}: ${data.name}`);
      
      if (data.role === "admin") {
        window.location.href = "admin-orders.html";
      } else {
        window.location.href = "index.html";
      }
    } else {
      alert(data.message || "Login failed");
    }
  } catch (err) {
    console.error("Connection error:", err);
    alert(`Error connecting to server: ${err.message}`);
  }
});