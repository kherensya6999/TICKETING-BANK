import { useState } from 'react';
import { useAppDispatch } from '../store/hooks';
import { useNavigate, Link } from 'react-router-dom';
import { register } from '../store/slices/authSlice';
import './Register.css';

export default function Register() {
  const [formData, setFormData] = useState({
    employee_id: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    first_name: '',
    last_name: '',
    phone: '',
    role: 'USER' as 'USER' | 'ADMIN',
    admin_code: '',
    department_id: '',
    branch_id: '',
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const dispatch = useAppDispatch();
  const navigate = useNavigate();

  const validateForm = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.employee_id.trim()) {
      newErrors.employee_id = 'Employee ID is required';
    }

    if (!formData.username.trim()) {
      newErrors.username = 'Username is required';
    } else if (!/^[a-zA-Z0-9_]+$/.test(formData.username)) {
      newErrors.username = 'Username can only contain letters, numbers, and underscores';
    }

    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Invalid email format';
    }

    if (!formData.password) {
      newErrors.password = 'Password is required';
    } else if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters';
    } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*#?&])/.test(formData.password)) {
      newErrors.password = 'Password must contain uppercase, lowercase, number, and special character';
    }

    if (formData.password !== formData.password_confirmation) {
      newErrors.password_confirmation = 'Passwords do not match';
    }

    if (!formData.first_name.trim()) {
      newErrors.first_name = 'First name is required';
    }

    if (!formData.last_name.trim()) {
      newErrors.last_name = 'Last name is required';
    }

    if (formData.role === 'ADMIN' && !formData.admin_code.trim()) {
      newErrors.admin_code = 'Admin code is required for admin registration';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});
    setSuccess(false);

    if (!validateForm()) {
      return;
    }

    setLoading(true);

    try {
      const registerData: any = {
        employee_id: formData.employee_id,
        username: formData.username,
        email: formData.email,
        password: formData.password,
        password_confirmation: formData.password_confirmation,
        first_name: formData.first_name,
        last_name: formData.last_name,
        role: formData.role,
      };

      if (formData.phone) {
        registerData.phone = formData.phone;
      }

      if (formData.role === 'ADMIN') {
        registerData.admin_code = formData.admin_code;
      }

      if (formData.department_id) {
        registerData.department_id = parseInt(formData.department_id);
      }

      if (formData.branch_id) {
        registerData.branch_id = parseInt(formData.branch_id);
      }

      const result: any = await dispatch(register(registerData)).unwrap();
      setSuccess(true);
      
      setTimeout(() => {
        navigate('/login');
      }, 2000);
    } catch (err: any) {
      if (err.errors) {
        setErrors(err.errors);
      } else {
        setErrors({ general: err.message || 'Registration failed. Please try again.' });
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="register-container">
      <div className="register-background">
        <div className="register-shapes">
          <div className="shape shape-1"></div>
          <div className="shape shape-2"></div>
          <div className="shape shape-3"></div>
          <div className="shape shape-4"></div>
        </div>
      </div>

      <div className="register-card">
        <div className="register-header">
          <div className="register-logo">
            <div className="logo-circle">🏦</div>
          </div>
          <h1>Bank Sumut</h1>
          <p className="register-subtitle">Create Your Account</p>
        </div>

        {success ? (
          <div className="success-message">
            <div className="success-icon">✅</div>
            <h3>Registration Successful!</h3>
            <p>
              {formData.role === 'ADMIN' 
                ? 'Your admin account has been created. You can now login.'
                : 'Your account has been created and is pending approval. You will be notified once approved.'}
            </p>
            <p className="redirect-text">Redirecting to login...</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="register-form">
            <div className="form-section">
              <h3 className="section-title">Account Information</h3>
              
              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="employee_id">Employee ID *</label>
                  <input
                    id="employee_id"
                    type="text"
                    value={formData.employee_id}
                    onChange={(e) => setFormData({ ...formData, employee_id: e.target.value })}
                    required
                    placeholder="EMP001"
                    className={errors.employee_id ? 'form-input error' : 'form-input'}
                    disabled={loading}
                  />
                  {errors.employee_id && <span className="error-text">{errors.employee_id}</span>}
                </div>

                <div className="form-group">
                  <label htmlFor="username">Username *</label>
                  <input
                    id="username"
                    type="text"
                    value={formData.username}
                    onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                    required
                    placeholder="johndoe"
                    className={errors.username ? 'form-input error' : 'form-input'}
                    disabled={loading}
                  />
                  {errors.username && <span className="error-text">{errors.username}</span>}
                </div>
              </div>

              <div className="form-group">
                <label htmlFor="email">Email Address *</label>
                <input
                  id="email"
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  required
                  placeholder="john.doe@banksumut.co.id"
                  className={errors.email ? 'form-input error' : 'form-input'}
                  disabled={loading}
                />
                {errors.email && <span className="error-text">{errors.email}</span>}
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="password">Password *</label>
                  <input
                    id="password"
                    type="password"
                    value={formData.password}
                    onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                    required
                    placeholder="Min. 8 characters"
                    className={errors.password ? 'form-input error' : 'form-input'}
                    disabled={loading}
                  />
                  {errors.password && <span className="error-text">{errors.password}</span>}
                  <div className="password-hint">
                    Must contain: uppercase, lowercase, number, and special character
                  </div>
                </div>

                <div className="form-group">
                  <label htmlFor="password_confirmation">Confirm Password *</label>
                  <input
                    id="password_confirmation"
                    type="password"
                    value={formData.password_confirmation}
                    onChange={(e) => setFormData({ ...formData, password_confirmation: e.target.value })}
                    required
                    placeholder="Re-enter password"
                    className={errors.password_confirmation ? 'form-input error' : 'form-input'}
                    disabled={loading}
                  />
                  {errors.password_confirmation && <span className="error-text">{errors.password_confirmation}</span>}
                </div>
              </div>
            </div>

            <div className="form-section">
              <h3 className="section-title">Personal Information</h3>
              
              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="first_name">First Name *</label>
                  <input
                    id="first_name"
                    type="text"
                    value={formData.first_name}
                    onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
                    required
                    placeholder="John"
                    className={errors.first_name ? 'form-input error' : 'form-input'}
                    disabled={loading}
                  />
                  {errors.first_name && <span className="error-text">{errors.first_name}</span>}
                </div>

                <div className="form-group">
                  <label htmlFor="last_name">Last Name *</label>
                  <input
                    id="last_name"
                    type="text"
                    value={formData.last_name}
                    onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
                    required
                    placeholder="Doe"
                    className={errors.last_name ? 'form-input error' : 'form-input'}
                    disabled={loading}
                  />
                  {errors.last_name && <span className="error-text">{errors.last_name}</span>}
                </div>
              </div>

              <div className="form-group">
                <label htmlFor="phone">Phone Number</label>
                <input
                  id="phone"
                  type="tel"
                  value={formData.phone}
                  onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                  placeholder="+62 812-3456-7890"
                  className="form-input"
                  disabled={loading}
                />
              </div>
            </div>

            <div className="form-section">
              <h3 className="section-title">Account Type</h3>
              
              <div className="form-group">
                <label htmlFor="role">Role *</label>
                <select
                  id="role"
                  value={formData.role}
                  onChange={(e) => setFormData({ ...formData, role: e.target.value as 'USER' | 'ADMIN', admin_code: '' })}
                  className="form-select"
                  disabled={loading}
                >
                  <option value="USER">User (End User)</option>
                  <option value="ADMIN">Admin (System Administrator)</option>
                </select>
              </div>

              {formData.role === 'ADMIN' && (
                <div className="form-group">
                  <label htmlFor="admin_code">Admin Registration Code *</label>
                  <input
                    id="admin_code"
                    type="password"
                    value={formData.admin_code}
                    onChange={(e) => setFormData({ ...formData, admin_code: e.target.value })}
                    required
                    placeholder="Enter admin code"
                    className={errors.admin_code ? 'form-input error' : 'form-input'}
                    disabled={loading}
                  />
                  {errors.admin_code && <span className="error-text">{errors.admin_code}</span>}
                  <div className="admin-hint">
                    🔒 Admin registration requires a special code. Contact system administrator.
                  </div>
                </div>
              )}
            </div>

            {errors.general && (
              <div className="alert alert-error">
                <span>⚠️</span>
                <span>{errors.general}</span>
              </div>
            )}

            <button
              type="submit"
              className="btn btn-primary btn-block"
              disabled={loading}
            >
              {loading ? (
                <>
                  <span className="spinner"></span>
                  Creating Account...
                </>
              ) : (
                <>
                  <span>✨</span>
                  Create Account
                </>
              )}
            </button>
          </form>
        )}

        <div className="register-footer">
          <p>
            Already have an account?{' '}
            <Link to="/login" className="link-primary">
              Login here
            </Link>
          </p>
          <p className="copyright">© 2026 Bank Sumut. All rights reserved.</p>
        </div>
      </div>
    </div>
  );
}
