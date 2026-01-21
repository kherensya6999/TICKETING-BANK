import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchTickets } from '../store/slices/ticketSlice';
import './Dashboard.css';

export default function Dashboard() {
  const dispatch = useAppDispatch();
  const { tickets } = useAppSelector((state) => state.tickets);
  const { user } = useAppSelector((state) => state.auth);

  useEffect(() => {
    dispatch(fetchTickets({}));
  }, [dispatch]);

  const stats = {
    total: tickets.length,
    new: tickets.filter((t: any) => t.status === 'NEW').length,
    inProgress: tickets.filter((t: any) => t.status === 'IN_PROGRESS').length,
    resolved: tickets.filter((t: any) => t.status === 'RESOLVED').length,
    critical: tickets.filter((t: any) => t.priority === 'CRITICAL').length,
  };

  return (
    <div className="dashboard">
      <div className="dashboard-header">
        <div>
          <h2>Welcome back, {user?.full_name?.split(' ')[0] || 'User'}! 👋</h2>
          <p>Here's what's happening with your tickets today</p>
        </div>
      </div>

      <div className="stats-grid">
        <div className="stat-card stat-primary">
          <div className="stat-icon">📊</div>
          <div className="stat-content">
            <div className="stat-label">Total Tickets</div>
            <div className="stat-value">{stats.total}</div>
          </div>
        </div>

        <div className="stat-card stat-warning">
          <div className="stat-icon">🆕</div>
          <div className="stat-content">
            <div className="stat-label">New Tickets</div>
            <div className="stat-value">{stats.new}</div>
          </div>
        </div>

        <div className="stat-card stat-info">
          <div className="stat-icon">⚙️</div>
          <div className="stat-content">
            <div className="stat-label">In Progress</div>
            <div className="stat-value">{stats.inProgress}</div>
          </div>
        </div>

        <div className="stat-card stat-success">
          <div className="stat-icon">✅</div>
          <div className="stat-content">
            <div className="stat-label">Resolved</div>
            <div className="stat-value">{stats.resolved}</div>
          </div>
        </div>

        <div className="stat-card stat-danger">
          <div className="stat-icon">🚨</div>
          <div className="stat-content">
            <div className="stat-label">Critical</div>
            <div className="stat-value">{stats.critical}</div>
          </div>
        </div>
      </div>

      <div className="dashboard-content">
        <div className="dashboard-section">
          <h3>Recent Tickets</h3>
          <div className="tickets-preview">
            {tickets.slice(0, 5).map((ticket: any) => (
              <div key={ticket.id} className="ticket-preview-item">
                <div className="ticket-preview-header">
                  <span className="ticket-number">{ticket.ticket_number}</span>
                  <span className={`badge priority-${ticket.priority?.toLowerCase()}`}>
                    {ticket.priority}
                  </span>
                </div>
                <div className="ticket-preview-title">{ticket.subject}</div>
                <div className="ticket-preview-meta">
                  <span className={`badge status-${ticket.status?.toLowerCase().replace('_', '-')}`}>
                    {ticket.status}
                  </span>
                  <span className="ticket-date">
                    {new Date(ticket.created_at).toLocaleDateString()}
                  </span>
                </div>
              </div>
            ))}
            {tickets.length === 0 && (
              <div className="empty-state">
                <div className="empty-icon">📭</div>
                <p>No tickets yet. Create your first ticket to get started!</p>
              </div>
            )}
          </div>
        </div>

        <div className="dashboard-section">
          <h3>Quick Actions</h3>
          <div className="quick-actions">
            <a href="/tickets/create" className="action-card">
              <div className="action-icon">➕</div>
              <div className="action-title">Create Ticket</div>
            </a>
            <a href="/tickets" className="action-card">
              <div className="action-icon">📋</div>
              <div className="action-title">View All Tickets</div>
            </a>
            <a href="/security" className="action-card">
              <div className="action-icon">🔒</div>
              <div className="action-title">Security Incidents</div>
            </a>
            <a href="/reports" className="action-card">
              <div className="action-icon">📈</div>
              <div className="action-title">Reports</div>
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
