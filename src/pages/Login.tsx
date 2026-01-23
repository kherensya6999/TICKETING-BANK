import { useState } from 'react';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { useNavigate, Link } from 'react-router-dom';
import { login } from '../store/slices/authSlice';
import './Login.css';

export default function Login() {
  const [formData, setFormData] = useState({
    username: '',
    password: '',
  });
  
  const [formErrors, setFormErrors] = useState<Record<string, string>>({});
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { loading, error } = useAppSelector((state) => state.auth);

  const validateForm = () => {
    const newErrors: Record<string, string> = {};
    if (!formData.username.trim()) newErrors.username = 'Username is required';
    if (!formData.password) newErrors.password = 'Password is required';
    setFormErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormErrors({});

    if (!validateForm()) return;

    try {
      // 1. Eksekusi Login
      await dispatch(login(formData)).unwrap();

      // 2. Redirect ke Dashboard (Dashboard.tsx yang akan menangani tampilan Admin vs User)
      navigate('/dashboard'); 

    } catch (err) {
      console.error("Login failed:", err);
    }
  };

  return (
    <div className="login-container">
      <div className="login-background">
        <div className="login-shapes">
          <div className="shape shape-1"></div>
          <div className="shape shape-2"></div>
          <div className="shape shape-3"></div>
        </div>
      </div>

      <div className="login-card">
        <div className="login-header">
          <div className="login-logo">
            <div className="logo-circle">🏦</div>
          </div>
          <h1>Bank Sumut</h1>
          <p className="login-subtitle">IT Security Ticketing System</p>
        </div>

        <form onSubmit={handleSubmit} className="login-form">
          {error && (
            <div className="alert alert-error">
              <span>⚠️</span><span>{error}</span>
            </div>
          )}

          <div className="form-group">
            <label htmlFor="username">Username / Employee ID</label>
            <input
              id="username"
              type="text"
              value={formData.username}
              onChange={(e) => setFormData({ ...formData, username: e.target.value })}
              placeholder="Enter your username"
              className={formErrors.username ? 'form-input error' : 'form-input'}
              disabled={loading}
            />
          </div>

          <div className="form-group">
            <label htmlFor="password">Password</label>
            <input
              id="password"
              type="password"
              value={formData.password}
              onChange={(e) => setFormData({ ...formData, password: e.target.value })}
              placeholder="Enter your password"
              className={formErrors.password ? 'form-input error' : 'form-input'}
              disabled={loading}
            />
          </div>

          <button type="submit" className="btn btn-primary btn-block" disabled={loading}>
            {loading ? <span className="spinner"></span> : <span>🔐 Sign In</span>}
          </button>
        </form>

        <div className="login-footer">
          <p>Don't have an account? <Link to="/register" className="link-primary">Register here</Link></p>
          <p className="copyright">© 2026 Bank Sumut. All rights reserved.</p>
        </div>
      </div>
    </div>
  );
}