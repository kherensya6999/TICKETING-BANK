import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { authService } from '../../services/authService';

interface User {
  id: number;
  employee_id: string;
  username: string;
  email: string;
  full_name: string;
  role: string;
  permissions: string[];
  department?: string;
  branch?: string;
}

interface AuthState {
  token: string | null;
  user: User | null;
  isAuthenticated: boolean; // Tambahan penting untuk routing
  loading: boolean;
  error: string | null;
}

const initialState: AuthState = {
  token: localStorage.getItem('token'),
  user: null,
  isAuthenticated: !!localStorage.getItem('token'),
  loading: false,
  error: null,
};

export const login = createAsyncThunk(
  'auth/login',
  async (credentials: { username: string; password: string }, { rejectWithValue }) => {
    try {
      const response = await authService.login(credentials);
      // PERBAIKAN 1: Akses nested data (response.data.data)
      const data = response.data.data; 
      
      // Simpan token
      localStorage.setItem('token', data.token);
      
      return data; // Mengembalikan { token: '...', user: {...} }
    } catch (error: any) {
      // PERBAIKAN 2: Tangkap error dari backend
      return rejectWithValue(error.response?.data?.message || 'Login failed');
    }
  }
);

export const register = createAsyncThunk(
  'auth/register',
  async (data: {
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
  }, { rejectWithValue }) => {
    try {
      const response = await authService.register(data);
      // Backend register response mungkin berbeda strukturnya, tapi biasanya di wrapper data
      return response.data; 
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Registration failed');
    }
  }
);

export const logout = createAsyncThunk('auth/logout', async () => {
  try {
    await authService.logout();
  } catch (error) {
    // Ignore logout errors
  }
  localStorage.removeItem('token');
});

export const getMe = createAsyncThunk(
  'auth/me', 
  async (_, { rejectWithValue }) => {
    try {
      const response = await authService.getMe();
      // PERBAIKAN 3: Akses nested data untuk user info
      return response.data.data; 
    } catch (error: any) {
      if (error.response?.status === 401) {
        localStorage.removeItem('token');
      }
      return rejectWithValue(error.response?.data?.message || 'Failed to get user info');
    }
  }
);

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    // Login Handling
    builder
      .addCase(login.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(login.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        state.token = action.payload.token;
        state.user = action.payload.user;
        state.isAuthenticated = true;
      })
      .addCase(login.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string; // Pesan error dari rejectWithValue
        state.isAuthenticated = false;
      });

    // Register Handling
    builder
      .addCase(register.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(register.fulfilled, (state) => {
        state.loading = false;
        state.error = null;
      })
      .addCase(register.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });

    // Logout Handling
    builder.addCase(logout.fulfilled, (state) => {
      state.token = null;
      state.user = null;
      state.isAuthenticated = false;
    });

    // Get Me Handling
    builder
      .addCase(getMe.fulfilled, (state, action: PayloadAction<any>) => {
        state.user = action.payload; // Payload sudah berisi data user bersih
        state.isAuthenticated = true;
      })
      .addCase(getMe.rejected, (state) => {
        state.user = null;
        state.token = null;
        state.isAuthenticated = false;
        // Token dihapus di thunk jika 401
      });
  },
});

export const { clearError } = authSlice.actions;
export default authSlice.reducer;