import api from './api';

export const authService = {
  login: async (credentials: { username: string; password: string }) => {
    return api.post('/auth/login', credentials);
  },
  
  register: async (data: {
    employee_id: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
    first_name: string;
    last_name: string;
    phone?: string;
    role: 'USER' | 'ADMIN';
    admin_code?: string;
    department_id?: number;
    branch_id?: number;
  }) => {
    return api.post('/auth/register', data);
  },

  logout: async () => {
    return api.post('/auth/logout');
  },

  getMe: async () => {
    return api.get('/auth/me');
  },
};