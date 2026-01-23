import { useEffect, useState } from 'react';
import { useAppSelector, useAppDispatch } from '../store/hooks';
import { fetchTickets } from '../store/slices/ticketSlice';
import { Link } from 'react-router-dom';
import './Dashboard.css';

export default function Dashboard() {
  const dispatch = useAppDispatch();
  const { user } = useAppSelector((state) => state.auth);
  const { tickets, loading } = useAppSelector((state) => state.tickets);
  const [currentTime, setCurrentTime] = useState(new Date());

  useEffect(() => {
    const timeInterval = setInterval(() => setCurrentTime(new Date()), 1000);
    dispatch(fetchTickets({ page: 1, per_page: 100 }));
    
    const dataInterval = setInterval(() => {
      dispatch(fetchTickets({ page: 1, per_page: 100 }));
    }, 30000);

    return () => {
      clearInterval(timeInterval);
      clearInterval(dataInterval);
    };
  }, [dispatch]);

  const AdminView = () => {
    // Logic Statistik
    const pending = tickets.filter(t => t.status === 'NEW' || t.status === 'PENDING').length;
    const progress = tickets.filter(t => t.status === 'IN_PROGRESS' || t.status === 'ASSIGNED').length;
    const resolved = tickets.filter(t => t.status === 'RESOLVED' || t.status === 'CLOSED').length;
    const urgent = tickets.filter(t => t.priority === 'CRITICAL' || t.priority === 'HIGH').length;

    return (
      <div className="dashboard-container">
        {/* HEADER */}
        <div className="dashboard-header">
          <div>
            <h1 className="header-title">IT Security Command Center</h1>
            <p className="header-subtitle">Overview & Monitoring Real-time</p>
          </div>
          {/* IMPLEMENTASI TOMBOL ORANGE (Tip No. 2) */}
          <Link to="/tickets/create" className="btn-create-ticket">
            <span className="icon-plus">+</span> Buat Tiket Baru
          </Link>
        </div>

        {/* METRICS GRID */}
        <div className="metrics-grid">
          <div className="metric-card card-warning">
            <div className="metric-header">
              <div className="metric-icon-box">⏳</div>
              <span className="metric-label">Menunggu</span>
            </div>
            <div className="metric-value">{pending}</div>
          </div>
          <div className="metric-card card-info">
            <div className="metric-header">
              <div className="metric-icon-box">⚙️</div>
              <span className="metric-label">Proses</span>
            </div>
            <div className="metric-value">{progress}</div>
          </div>
          <div className="metric-card card-success">
            <div className="metric-header">
              <div className="metric-icon-box">✅</div>
              <span className="metric-label">Selesai</span>
            </div>
            <div className="metric-value">{resolved}</div>
          </div>
          <div className="metric-card card-danger">
            <div className="metric-header">
              <div className="metric-icon-box">🚨</div>
              <span className="metric-label">Urgent</span>
            </div>
            <div className="metric-value">{urgent}</div>
          </div>
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '24px', marginBottom: '32px' }}>
          {/* RECENT TICKETS TABLE */}
          <div className="dashboard-card">
            <div className="card-header-flex">
              <h3>📝 Tiket Terbaru</h3>
              <Link to="/tickets" style={{ color: '#2563eb', fontWeight: 600, textDecoration: 'none' }}>Lihat Semua →</Link>
            </div>
            <div style={{ overflowX: 'auto' }}>
              <table className="modern-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Subjek</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr><td colSpan={5} className="text-center p-4">Loading...</td></tr>
                  ) : tickets.slice(0, 5).map((t) => (
                    <tr key={t.id}>
                      <td style={{ fontFamily: 'monospace', fontWeight: 600, color: '#3b82f6' }}>
                        #{t.ticket_number || t.id}
                      </td>
                      <td style={{ fontWeight: 600 }}>{t.subject}</td>
                      <td>
                        {/* IMPLEMENTASI BADGE COLORS (Tip No. 3) */}
                        <span className={`priority-badge priority-${t.priority?.toLowerCase()}`}>
                          {t.priority}
                        </span>
                      </td>
                      <td>
                        <span className={`status-pill status-${t.status?.toLowerCase()}`}>
                          {t.status?.replace('_', ' ')}
                        </span>
                      </td>
                      <td>
                        <Link to={`/tickets/${t.id}`} className="btn-icon">👁️</Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          {/* QUICK ACTIONS */}
          <div className="dashboard-card">
            <div style={{ marginBottom: '20px', borderBottom: '1px solid #f1f5f9', paddingBottom: '15px' }}>
              <h3 style={{ margin: 0, fontSize: '1.1rem' }}>⚡ Aksi Cepat</h3>
            </div>
            <div className="quick-actions-list">
              <Link to="/tickets?priority=CRITICAL" className="action-btn">
                <div style={{ width: '40px', fontSize: '1.5rem' }}>🔥</div>
                <div>
                  <strong style={{ color: '#ef4444' }}>Tiket Urgent ({urgent})</strong>
                  <div style={{ fontSize: '0.8rem', color: '#64748b' }}>Butuh penanganan prioritas</div>
                </div>
              </Link>
              <Link to="/reports" className="action-btn">
                <div style={{ width: '40px', fontSize: '1.5rem' }}>📊</div>
                <div>
                  <strong style={{ color: '#1e293b' }}>Generate Report</strong>
                  <div style={{ fontSize: '0.8rem', color: '#64748b' }}>Unduh laporan performa</div>
                </div>
              </Link>
              <Link to="/users" className="action-btn">
                <div style={{ width: '40px', fontSize: '1.5rem' }}>👥</div>
                <div>
                  <strong style={{ color: '#1e293b' }}>Manajemen User</strong>
                  <div style={{ fontSize: '0.8rem', color: '#64748b' }}>Kelola akses teknisi</div>
                </div>
              </Link>
            </div>
          </div>
        </div>
      </div>
    );
  };

  const UserView = () => (
    <div className="dashboard-container">
      <div style={{ textAlign: 'center', padding: '60px 20px', background: 'white', borderRadius: '16px', border: '1px solid #e2e8f0' }}>
        <h1 style={{ fontSize: '2.5rem', color: '#1e293b', marginBottom: '16px' }}>
          Halo, {user?.full_name?.split(' ')[0]}! 👋
        </h1>
        <p style={{ color: '#64748b', fontSize: '1.1rem', marginBottom: '32px' }}>
          Ada kendala IT? Laporkan segera di sini.
        </p>
        <Link to="/tickets/create" className="btn-create-ticket" style={{ display: 'inline-flex', width: 'auto' }}>
          <span className="icon-plus">+</span> Buat Tiket Baru
        </Link>
      </div>
    </div>
  );

  return (user?.role === 'SUPER_ADMIN' || user?.role === 'ADMIN') ? <AdminView /> : <UserView />;
}