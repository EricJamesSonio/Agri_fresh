document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  
  const email = e.target.email.value;
  const password = e.target.password.value;
  
  try {
    console.log("Attempting to connect to:", apiUrl("login")); // Debug line
    
    const res = await fetch(apiUrl("login"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password })
    });
    
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    
    const data = await res.json();
    
    if (data.status === "success") {
      alert(`Welcome ${data.role}: ${data.name}`);
      if (data.role === "admin") {
        window.location.href = "admin.html";
      } else {
        window.location.href = "index.html";
      }
    } else {
      alert(data.message);
    }
  } catch (err) {
    console.error("Connection error:", err);
    alert(`Error connecting to server: ${err.message}`);
  }
});