import { Outlet, Link, useNavigate, useLocation } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../store/hooks';
import { logout } from '../../store/slices/authSlice';
import './Layout.css';

export default function Layout() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const { user } = useAppSelector((state) => state.auth);
  const { unreadCount } = useAppSelector((state) => state.notifications);

  const handleLogout = () => {
    dispatch(logout());
    navigate('/login');
  };

  const isActive = (path: string) => location.pathname === path;

  return (
    <div className="layout">
      <aside className="sidebar">
        <div className="sidebar-header">
          <div className="logo">
            <div className="logo-icon">🏦</div>
            <div className="logo-text">
              <h2>Bank Sumut</h2>
              <p>IT Ticketing</p>
            </div>
          </div>
        </div>
        
        <nav className="sidebar-nav">
          <Link 
            to="/dashboard" 
            className={`nav-item ${isActive('/dashboard') ? 'active' : ''}`}
          >
            <span className="nav-icon">📊</span>
            <span>Dashboard</span>
          </Link>
          
          <Link 
            to="/tickets" 
            className={`nav-item ${isActive('/tickets') ? 'active' : ''}`}
          >
            <span className="nav-icon">🎫</span>
            <span>Tickets</span>
            {unreadCount > 0 && (
              <span className="nav-badge">{unreadCount}</span>
            )}
          </Link>
          
          <Link 
            to="/tickets/create" 
            className={`nav-item ${isActive('/tickets/create') ? 'active' : ''}`}
          >
            <span className="nav-icon">➕</span>
            <span>Create Ticket</span>
          </Link>
          
          <Link 
            to="/security" 
            className={`nav-item ${isActive('/security') ? 'active' : ''}`}
          >
            <span className="nav-icon">🔒</span>
            <span>Security Incidents</span>
          </Link>
          
          <Link 
            to="/reports" 
            className={`nav-item ${isActive('/reports') ? 'active' : ''}`}
          >
            <span className="nav-icon">📈</span>
            <span>Reports</span>
          </Link>

          {(user?.role === 'Admin' || user?.role === 'SUPER_ADMIN') && (
            <Link 
              to="/admin/users" 
              className={`nav-item ${isActive('/admin/users') ? 'active' : ''}`}
            >
              <span className="nav-icon">👥</span>
              <span>User Management</span>
            </Link>
          )}
        </nav>

        <div className="sidebar-footer">
          <div className="user-info">
            <div className="user-avatar">
              {user?.full_name?.charAt(0) || 'U'}
            </div>
            <div className="user-details">
              <div className="user-name">{user?.full_name || 'User'}</div>
              <div className="user-role">{user?.role || 'User'}</div>
            </div>
          </div>
          <button onClick={handleLogout} className="btn-logout">
            🚪 Logout
          </button>
        </div>
      </aside>

      <main className="main-content">
        <header className="top-header">
          <div className="header-left">
            <h1 className="page-title">
              {location.pathname === '/dashboard' && 'Dashboard'}
              {location.pathname === '/tickets' && 'Tickets'}
              {location.pathname === '/tickets/create' && 'Create New Ticket'}
              {location.pathname.startsWith('/tickets/') && !location.pathname.includes('/create') && 'Ticket Details'}
              {location.pathname === '/security' && 'Security Incidents'}
              {location.pathname === '/reports' && 'Reports'}
            </h1>
          </div>
          <div className="header-right">
            <div className="notifications">
              <span className="notification-icon">🔔</span>
              {unreadCount > 0 && (
                <span className="notification-badge">{unreadCount}</span>
              )}
            </div>
          </div>
        </header>

        <div className="content-wrapper">
          <Outlet />
        </div>
      </main>
    </div>
  );
}
