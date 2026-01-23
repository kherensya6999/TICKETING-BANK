import api from './api';

export interface UserData {
  id: number;
  employee_id: string;
  username: string;
  email: string;
  first_name: string;
  last_name: string;
  role: { role_name: string; role_code: string };
  department?: { department_name: string };
  is_active: boolean;
  created_at: string;
}

export interface InviteResponse {
  email: string;
  token: string;
  expires_in: string;
}

const adminService = {
  // --- User Management ---
  getUsers: (page = 1, search = '', status = '') => {
    return api.get(`/admin/users?page=${page}&search=${search}&status=${status}`);
  },

  approveUser: (userId: number) => {
    return api.post(`/admin/users/${userId}/approve`);
  },

  inviteAdmin: (email: string) => {
    return api.post('/admin/invite', { email });
  },

  // --- Reports & Analytics ---
  getTicketStats: (range: 'week' | 'month' | 'year') => {
    // Endpoint ini harusnya mengembalikan data statistik (misal: jumlah tiket per kategori, performa SLA)
    return api.get(`/admin/reports/stats?range=${range}`);
  },

  exportReport: (format: 'pdf' | 'excel', filters: any) => {
    return api.post(`/admin/reports/export`, { format, ...filters }, { responseType: 'blob' });
  },

  // --- System Settings ---
  getSystemSettings: () => {
    return api.get('/admin/settings');
  },

  updateSystemSettings: (settings: any) => {
    return api.post('/admin/settings', settings);
  }
};

export default adminService;