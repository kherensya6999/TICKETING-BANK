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

  // Real-time Clock & Auto Refresh (Polling)
  useEffect(() => {
    const timeInterval = setInterval(() => setCurrentTime(new Date()), 1000);
    dispatch(fetchTickets({ page: 1, per_page: 100 })); // Initial Load
    
    const dataInterval = setInterval(() => {
      dispatch(fetchTickets({ page: 1, per_page: 100 }));
    }, 30000); // Update tiap 30 detik

    return () => {
      clearInterval(timeInterval);
      clearInterval(dataInterval);
    };
  }, [dispatch]);

  // --- LOGIC: PERHITUNGAN STATISTIK ---
  const total = tickets.length || 1; // Avoid division by zero
  const pending = tickets.filter(t => t.status === 'NEW' || t.status === 'PENDING').length;
  const progress = tickets.filter(t => t.status === 'IN_PROGRESS' || t.status === 'ASSIGNED').length;
  const resolved = tickets.filter(t => t.status === 'RESOLVED' || t.status === 'CLOSED').length;
  const urgent = tickets.filter(t => t.priority === 'CRITICAL' || t.priority === 'HIGH').length;

  // Persentase untuk Pie Chart
  const pctPending = (pending / total) * 100;
  const pctProgress = (progress / total) * 100;
  const pctResolved = (resolved / total) * 100;

  // Style untuk Pie Chart Dinamis
  const pieChartStyle = {
    background: `conic-gradient(
      #f59e0b 0% ${pctPending}%, 
      #3b82f6 ${pctPending}% ${pctPending + pctProgress}%, 
      #10b981 ${pctPending + pctProgress}% 100%
    )`
  };

  const AdminView = () => (
    <div className="dashboard-container admin-theme">
      {/* HEADER */}
      <div className="dashboard-header">
        <div className="header-left">
          <h1 className="header-title">IT Security Command Center</h1>
          <p className="header-subtitle">Monitoring Keamanan & Insiden Operasional</p>
        </div>
        <div className="header-right">
          <div className="live-clock">
            <div className="clock-time">{currentTime.toLocaleTimeString('id-ID')}</div>
            <div className="clock-date">{currentTime.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}</div>
          </div>
        </div>
      </div>

      {/* TOP STATS CARDS */}
      <div className="metrics-grid">
        <div className="metric-card card-warning">
          <div className="metric-icon-bg">⏳</div>
          <div>
            <p className="metric-label">Menunggu Respon</p>
            <h3 className="metric-value">{pending}</h3>
          </div>
        </div>
        <div className="metric-card card-info">
          <div className="metric-icon-bg">⚙️</div>
          <div>
            <p className="metric-label">Sedang Dikerjakan</p>
            <h3 className="metric-value">{progress}</h3>
          </div>
        </div>
        <div className="metric-card card-success">
          <div className="metric-icon-bg">✅</div>
          <div>
            <p className="metric-label">Selesai Hari Ini</p>
            <h3 className="metric-value">{resolved}</h3>
          </div>
        </div>
        <div className="metric-card card-danger">
          <div className="metric-icon-bg">🚨</div>
          <div>
            <p className="metric-label">Tiket Urgent</p>
            <h3 className="metric-value">{urgent}</h3>
          </div>
        </div>
      </div>

      {/* ANALYTICS SECTION */}
      <div className="content-grid-dashboard">
        {/* CHART CARD */}
        <div className="dashboard-card chart-card">
          <div className="card-header">
            <h3>📊 Distribusi Status Tiket</h3>
          </div>
          <div className="chart-wrapper">
            <div className="pie-chart" style={pieChartStyle}>
              <div className="pie-center">
                <span>Total</span>
                <strong>{total}</strong>
              </div>
            </div>
            <div className="chart-legend">
              <div className="legend-item"><span className="dot yellow"></span> Pending ({Math.round(pctPending)}%)</div>
              <div className="legend-item"><span className="dot blue"></span> In Progress ({Math.round(pctProgress)}%)</div>
              <div className="legend-item"><span className="dot green"></span> Resolved ({Math.round(pctResolved)}%)</div>
            </div>
          </div>
        </div>

        {/* QUICK ACTIONS CARD */}
        <div className="dashboard-card actions-card">
          <div className="card-header">
            <h3>⚡ Quick Actions</h3>
          </div>
          <div className="quick-actions-list">
            <Link to="/tickets?priority=CRITICAL" className="action-btn btn-urgent">
              <span className="icon">🔥</span>
              <div className="text">
                <strong>Tangani Tiket Critical</strong>
                <small>{urgent} tiket butuh perhatian</small>
              </div>
              <span className="arrow">→</span>
            </Link>
            <Link to="/tickets?status=NEW" className="action-btn btn-new">
              <span className="icon">📩</span>
              <div className="text">
                <strong>Tiket Belum Dibaca</strong>
                <small>{pending} tiket baru masuk</small>
              </div>
              <span className="arrow">→</span>
            </Link>
            <Link to="/reports" className="action-btn btn-report">
              <span className="icon">📑</span>
              <div className="text">
                <strong>Generate Report</strong>
                <small>Unduh laporan bulanan</small>
              </div>
              <span className="arrow">→</span>
            </Link>
          </div>
        </div>
      </div>

      {/* RECENT TICKETS TABLE */}
      <div className="dashboard-card table-section">
        <div className="card-header-flex">
          <h3>📝 Tiket Terbaru</h3>
          <Link to="/tickets" className="link-view-all">Lihat Semua</Link>
        </div>
        <div className="table-responsive">
          <table className="modern-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Judul Insiden</th>
                <th>Status</th>
                <th>Prioritas</th>
                <th>Pelapor</th>
                <th>Waktu</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan={7} className="text-center">Memuat data...</td></tr>
              ) : tickets.slice(0, 5).map((t) => (
                <tr key={t.id}>
                  <td className="font-mono">#{t.ticket_number || t.id}</td>
                  <td className="fw-bold">{t.subject}</td>
                  <td><span className={`status-pill status-${t.status?.toLowerCase()}`}>{t.status?.replace('_', ' ')}</span></td>
                  <td><span className={`priority-dot priority-${t.priority?.toLowerCase()}`}>● {t.priority}</span></td>
                  <td>{(t as any).requester?.full_name || 'Unknown'}</td>
                  <td className="text-muted">{new Date(t.created_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})}</td>
                  <td><Link to={`/tickets/${t.id}`} className="btn-icon">👁️</Link></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );

  const UserView = () => (
    <div className="dashboard-container user-theme">
      <div className="user-welcome-hero">
        <h1>Halo, {user?.full_name?.split(' ')[0]}! 👋</h1>
        <p>Laporkan kendala IT Anda agar segera kami tangani.</p>
        <div className="hero-cta">
          <Link to="/tickets/create" className="cta-btn primary">+ Buat Tiket Baru</Link>
          <Link to="/tickets" className="cta-btn secondary">Cek Tiket Saya</Link>
        </div>
      </div>
    </div>
  );

  return (user?.role === 'SUPER_ADMIN' || user?.role === 'ADMIN') ? <AdminView /> : <UserView />;
}