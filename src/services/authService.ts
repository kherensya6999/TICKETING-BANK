import api from './api';

export const authService = {
  // Login: Kirim username & password
  login: async (credentials: { username: string; password: string }) => {
    return api.post('/auth/login', credentials);
  },
  
  // Register: Kirim data pendaftaran lengkap
  register: async (data: any) => {
    return api.post('/auth/register', data);
  },

  // Logout: Request hapus session
  logout: async () => {
    return api.post('/auth/logout');
  },

  // Get Me: Ambil data user yang sedang login
  getMe: async () => {
    return api.get('/auth/me');
  },
};