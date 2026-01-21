import api from './api';

export const ticketService = {
  getTickets: async (params?: any) => {
    return api.get('/tickets', { params });
  },
  getTicket: async (id: number) => {
    return api.get(`/tickets/${id}`);
  },
  createTicket: async (data: any) => {
    const formData = new FormData();
    Object.keys(data).forEach((key) => {
      if (key === 'attachments' && Array.isArray(data[key])) {
        data[key].forEach((file: File) => {
          formData.append('attachments[]', file);
        });
      } else {
        formData.append(key, data[key]);
      }
    });
    return api.post('/tickets', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
  updateTicket: async (id: number, data: any) => {
    return api.put(`/tickets/${id}`, data);
  },
  resolveTicket: async (id: number, data: any) => {
    return api.post(`/tickets/${id}/resolve`, data);
  },
  addComment: async (id: number, data: any) => {
    const formData = new FormData();
    Object.keys(data).forEach((key) => {
      if (key === 'attachments' && Array.isArray(data[key])) {
        data[key].forEach((file: File) => {
          formData.append('attachments[]', file);
        });
      } else {
        formData.append(key, data[key]);
      }
    });
    return api.post(`/tickets/${id}/comments`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
};
