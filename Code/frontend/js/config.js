const CONFIG = {
  BASE_URL: "http://localhost/agri_fresh/code/backend/api",
  IMAGE_PATH: "http://localhost/agri_fresh/code/frontend/images"
};

function apiUrl(endpoint) {
  return `${CONFIG.BASE_URL}/${endpoint}`;
}

function imageUrl(filename) {
  return `${CONFIG.IMAGE_PATH}/${filename}`;
}

