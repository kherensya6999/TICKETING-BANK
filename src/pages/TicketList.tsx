import { useEffect, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchTickets } from '../store/slices/ticketSlice';
import { Link } from 'react-router-dom';
import './TicketList.css';

export default function TicketList() {
  const dispatch = useAppDispatch();
  const { tickets, loading } = useAppSelector((state) => state.tickets);
  const [filters, setFilters] = useState({
    status: '',
    priority: '',
    search: '',
  });

  useEffect(() => {
    dispatch(fetchTickets(filters));
  }, [dispatch, filters]);

  const handleFilterChange = (key: string, value: string) => {
    setFilters({ ...filters, [key]: value });
  };

  return (
    <div className="ticket-list-page">
      <div className="page-header">
        <div className="header-actions">
          <Link to="/tickets/create" className="btn btn-primary">
            <span>➕</span>
            Create New Ticket
          </Link>
        </div>
      </div>

      <div className="filters-bar">
        <div className="filter-group">
          <label>Status</label>
          <select
            value={filters.status}
            onChange={(e) => handleFilterChange('status', e.target.value)}
            className="filter-select"
          >
            <option value="">All Status</option>
            <option value="NEW">New</option>
            <option value="ASSIGNED">Assigned</option>
            <option value="IN_PROGRESS">In Progress</option>
            <option value="RESOLVED">Resolved</option>
            <option value="CLOSED">Closed</option>
          </select>
        </div>

        <div className="filter-group">
          <label>Priority</label>
          <select
            value={filters.priority}
            onChange={(e) => handleFilterChange('priority', e.target.value)}
            className="filter-select"
          >
            <option value="">All Priority</option>
            <option value="LOW">Low</option>
            <option value="MEDIUM">Medium</option>
            <option value="HIGH">High</option>
            <option value="URGENT">Urgent</option>
            <option value="CRITICAL">Critical</option>
          </select>
        </div>

        <div className="filter-group filter-search">
          <label>Search</label>
          <input
            type="text"
            placeholder="Search tickets..."
            value={filters.search}
            onChange={(e) => handleFilterChange('search', e.target.value)}
            className="filter-input"
          />
        </div>
      </div>

      {loading ? (
        <div className="loading-state">
          <div className="spinner-large"></div>
          <p>Loading tickets...</p>
        </div>
      ) : (
        <div className="tickets-table-container">
          <table className="tickets-table">
            <thead>
              <tr>
                <th>Ticket Number</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Category</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {tickets.map((ticket: any) => (
                <tr key={ticket.id}>
                  <td>
                    <Link to={`/tickets/${ticket.id}`} className="ticket-link">
                      {ticket.ticket_number}
                    </Link>
                  </td>
                  <td>
                    <div className="ticket-subject">{ticket.subject}</div>
                    {ticket.is_security_incident && (
                      <span className="security-badge">🔒 Security</span>
                    )}
                  </td>
                  <td>
                    <span className={`badge status-${ticket.status?.toLowerCase().replace('_', '-')}`}>
                      {ticket.status}
                    </span>
                  </td>
                  <td>
                    <span className={`badge priority-${ticket.priority?.toLowerCase()}`}>
                      {ticket.priority}
                    </span>
                  </td>
                  <td>{ticket.category?.category_name || 'N/A'}</td>
                  <td>{new Date(ticket.created_at).toLocaleDateString()}</td>
                  <td>
                    <Link
                      to={`/tickets/${ticket.id}`}
                      className="btn-action"
                    >
                      View
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {tickets.length === 0 && (
            <div className="empty-state">
              <div className="empty-icon">📭</div>
              <h3>No tickets found</h3>
              <p>Try adjusting your filters or create a new ticket</p>
              <Link to="/tickets/create" className="btn btn-primary">
                Create Ticket
              </Link>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
