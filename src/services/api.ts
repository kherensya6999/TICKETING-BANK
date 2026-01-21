import axios from 'axios';

// Pastikan base URL mengarah ke Backend Laravel yang sedang jalan
const api = axios.create({
  baseURL: 'http://localhost:8000/api', // Sesuaikan port backend Anda
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor: Setiap mau request, cek token dulu
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token'); // Ambil token dari penyimpanan
    if (token) {
      config.headers.Authorization = `Bearer ${token}`; // Tempel token
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor: Kalau response 401 (Unauthorized), tendang ke login
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token'); // Hapus token kadaluarsa
      // Opsi: window.location.href = '/login'; 
    }
    return Promise.reject(error);
  }
);

export default api;