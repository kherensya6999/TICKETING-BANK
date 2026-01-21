import api from './api';

export const categoryService = {
  getCategories: async () => {
    return api.get('/ticket-categories');
  },
  getSubcategories: async (categoryId: number) => {
    return api.get(`/ticket-categories/${categoryId}/subcategories`);
  },
};
