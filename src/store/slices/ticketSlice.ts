import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { ticketService } from '../../services/ticketService';

interface Ticket {
  id: number;
  ticket_number: string;
  subject: string;
  status: string;
  priority: string;
  category: any;
  requester: any;
  assigned_to: any;
  created_at: string;
  due_date: string;
}

interface TicketState {
  tickets: Ticket[];
  currentTicket: Ticket | null;
  loading: boolean;
  error: string | null;
}

const initialState: TicketState = {
  tickets: [],
  currentTicket: null,
  loading: false,
  error: null,
};

export const fetchTickets = createAsyncThunk(
  'tickets/fetchTickets',
  async (params?: any) => {
    const response = await ticketService.getTickets(params);
    return response.data;
  }
);

export const fetchTicket = createAsyncThunk(
  'tickets/fetchTicket',
  async (id: number) => {
    const response = await ticketService.getTicket(id);
    return response.data;
  }
);

export const createTicket = createAsyncThunk(
  'tickets/createTicket',
  async (data: any) => {
    const response = await ticketService.createTicket(data);
    return response.data;
  }
);

const ticketSlice = createSlice({
  name: 'tickets',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchTickets.pending, (state) => {
        state.loading = true;
      })
      .addCase(fetchTickets.fulfilled, (state, action) => {
        state.loading = false;
        state.tickets = action.payload.data || [];
      })
      .addCase(fetchTickets.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch tickets';
      })
      .addCase(fetchTicket.fulfilled, (state, action) => {
        state.currentTicket = action.payload;
      })
      .addCase(createTicket.fulfilled, (state, action) => {
        state.tickets.unshift(action.payload);
      });
  },
});

export default ticketSlice.reducer;
