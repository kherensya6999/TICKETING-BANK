import { useState, useEffect } from 'react';
import { Outlet, Link, useLocation, useNavigate } from 'react-router-dom';
import { useAppSelector, useAppDispatch } from '../../store/hooks';
import { logout } from '../../store/slices/authSlice';
import { fetchTickets } from '../../store/slices/ticketSlice';
import './Layout.css';

export default function Layout() {
  const { user } = useAppSelector((state) => state.auth);
  const { tickets } = useAppSelector((state) => state.tickets);
  const dispatch = useAppDispatch();
  const location = useLocation();
  const navigate = useNavigate();
  
  const [showNotifications, setShowNotifications] = useState(false);

  // Ambil data tiket untuk menghitung badge
  useEffect(() => {
    dispatch(fetchTickets({ page: 1, per_page: 100 }));
  }, [dispatch]);

  const pendingCount = tickets.filter(t => t.status === 'NEW' || t.status === 'PENDING').length;
  const isAdmin = user?.role === 'SUPER_ADMIN' || user?.role === 'ADMIN';

  const handleLogout = async () => {
    await dispatch(logout());
    navigate('/login');
  };

  return (
    <div className="app-layout">
      {/* SIDEBAR */}
      <aside className="sidebar">
        <div className="sidebar-brand">
          <div className="logo-icon">🏦</div>
          <span className="brand-name">Bank Sumut IT</span>
        </div>

        <nav className="sidebar-nav">
          <div className="nav-group">
            <p className="nav-title">MAIN MENU</p>
            <Link to="/dashboard" className={`nav-item ${location.pathname === '/dashboard' ? 'active' : ''}`}>
              <span className="icon">📊</span> Dashboard
            </Link>
            
            <Link to="/tickets" className={`nav-item ${location.pathname.includes('/tickets') ? 'active' : ''}`}>
              <span className="icon">🎫</span> Tickets
              {pendingCount > 0 && <span className="badge-count">{pendingCount}</span>}
            </Link>
          </div>

          {isAdmin && (
            <div className="nav-group">
              <p className="nav-title">ADMINISTRATION</p>
              <Link to="/users" className={`nav-item ${location.pathname === '/users' ? 'active' : ''}`}>
                <span className="icon">👥</span> Users
              </Link>
              <Link to="/reports" className={`nav-item ${location.pathname === '/reports' ? 'active' : ''}`}>
                <span className="icon">📈</span> Reports
              </Link>
              <Link to="/settings" className={`nav-item ${location.pathname === '/settings' ? 'active' : ''}`}>
                <span className="icon">⚙️</span> Settings
              </Link>
            </div>
          )}
        </nav>

        <div className="sidebar-footer">
          <button onClick={handleLogout} className="btn-logout">
            <span className="icon">🚪</span> Logout
          </button>
        </div>
      </aside>

      {/* MAIN CONTENT AREA */}
      <main className="main-content">
        {/* TOP HEADER */}
        <header className="top-header">
          <div className="header-search">
            <span className="search-icon">🔍</span>
            <input type="text" placeholder="Search anything..." />
          </div>
          
          <div className="header-actions">
            {/* NOTIFICATION BELL */}
            <div className="notification-wrapper">
              <button className="btn-icon" onClick={() => setShowNotifications(!showNotifications)}>
                🔔
                {pendingCount > 0 && <span className="notif-dot"></span>}
              </button>
              
              {/* DROPDOWN NOTIFIKASI */}
              {showNotifications && (
                <div className="notification-dropdown">
                  <div className="dropdown-header">
                    <h4>Notifikasi</h4>
                    <button onClick={() => setShowNotifications(false)}>✕</button>
                  </div>
                  <div className="dropdown-body">
                    {pendingCount > 0 ? (
                      <div className="notif-item unread">
                        <div className="notif-icon">🔥</div>
                        <div className="notif-text">
                          <p><strong>{pendingCount} Tiket Baru</strong></p>
                          <small>Butuh penanganan segera.</small>
                        </div>
                      </div>
                    ) : (
                      <div className="empty-state">Tidak ada notifikasi baru</div>
                    )}
                  </div>
                  <Link to="/tickets" className="dropdown-footer">Lihat Semua</Link>
                </div>
              )}
            </div>

            {/* USER PROFILE */}
            <div className="user-profile-header">
              <div className="avatar">
                {user?.full_name?.charAt(0) || 'U'}
              </div>
              <div className="user-info">
                <span className="name">{user?.full_name?.split(' ')[0]}</span>
                <span className="role">{user?.role?.replace('_', ' ')}</span>
              </div>
            </div>
          </div>
        </header>

        {/* PAGE CONTENT */}
        <div className="page-wrapper">
          <Outlet />
        </div>
      </main>
    </div>
  );
}