function loadSection(section) {
  const main = document.getElementById("main-content");

  // Fetch the section HTML
  fetch(`sections/${section}.html`)
    .then(res => res.text())
    .then(html => {
      main.innerHTML = html;

      // After HTML is loaded, append the section JS
      const script = document.createElement("script");
      script.src = `js/${section}.js`; // must be relative to admin/index.html
      document.body.appendChild(script);
    })
    .catch(err => console.error("Failed to load section:", err));
}
