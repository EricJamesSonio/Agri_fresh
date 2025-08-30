const CONFIG = {
  BASE_URL: "http://localhost/agri_fresh/code/backend/api"
};

function apiUrl(endpoint) {
  return `${CONFIG.BASE_URL}/${endpoint}`; 
  // htaccess rewrites "/login" -> index.php?request=login
}
