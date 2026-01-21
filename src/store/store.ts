import { configureStore } from '@reduxjs/toolkit';
import authSlice from './slices/authSlice';
import ticketSlice from './slices/ticketSlice';
import notificationSlice from './slices/notificationSlice';

export const store = configureStore({
  reducer: {
    auth: authSlice,
    tickets: ticketSlice,
    notifications: notificationSlice,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
