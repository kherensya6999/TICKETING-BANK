import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { authService } from '../../services/authService';

// Interface User (Sesuai dengan output AuthController.php)
interface User {
  id: number;
  employee_id: string;
  username: string;
  email: string;
  full_name: string;
  role: string;
  permissions: string[];
  department?: string | null;
  branch?: string | null;
}

interface AuthState {
  token: string | null;
  user: User | null;
  isAuthenticated: boolean;
  loading: boolean;
  error: string | null;
}

// Cek LocalStorage saat aplikasi pertama kali dimuat
const initialState: AuthState = {
  token: localStorage.getItem('token'),
  user: null, // User akan di-load ulang via getMe nanti
  isAuthenticated: !!localStorage.getItem('token'),
  loading: false,
  error: null,
};

// --- LOGIN THUNK ---
export const login = createAsyncThunk(
  'auth/login',
  async (credentials: { username: string; password: string }, { rejectWithValue }) => {
    try {
      const response = await authService.login(credentials);
      
      // DEBUG: Lihat isi respon di Console Browser (Tekan F12)
      console.log('Login Response from API:', response);

      const responseData = response.data;

      // Cek field 'success' dari backend
      if (!responseData.success) {
         return rejectWithValue(responseData.message || 'Login failed');
      }

      // Ambil token & user dari dalam objek 'data'
      const data = responseData.data;
      
      if (!data || !data.token) {
          throw new Error('Token tidak ditemukan dalam respon server.');
      }

      const { token, user } = data;
      
      // Simpan Token ke Browser
      localStorage.setItem('token', token);
      
      return { token, user }; 
    } catch (error: any) {
      console.error('Login Error:', error);
      // Tangkap pesan error dari backend (misal: "Invalid credentials")
      const message = error.response?.data?.message || error.message || 'Login gagal. Cek koneksi.';
      return rejectWithValue(message);
    }
  }
);

// --- REGISTER THUNK ---
export const register = createAsyncThunk(
  'auth/register',
  async (data: any, { rejectWithValue }) => {
    try {
      const response = await authService.register(data);
      return response.data; 
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Registration failed');
    }
  }
);

// --- LOGOUT THUNK ---
export const logout = createAsyncThunk('auth/logout', async () => {
  try {
    await authService.logout();
  } catch (error) {
    // Abaikan jika logout backend gagal, tetap hapus lokal
  }
  localStorage.removeItem('token');
});

// --- GET ME THUNK ---
export const getMe = createAsyncThunk(
  'auth/me', 
  async (_, { rejectWithValue }) => {
    try {
      const response = await authService.getMe();
      // Data user ada di response.data.data
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
        state.error = action.payload as string;
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
        state.user = action.payload;
        state.isAuthenticated = true;
      })
      .addCase(getMe.rejected, (state) => {
        state.user = null;
        state.token = null;
        state.isAuthenticated = false;
      });
  },
});

export const { clearError } = authSlice.actions;
export default authSlice.reducer;