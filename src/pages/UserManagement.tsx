import React, { useState, useEffect } from 'react';
import adminService, { UserData, InviteResponse } from '../services/adminService';
import './UserManagement.css';

export default function UserManagement() {
  const [activeTab, setActiveTab] = useState<'users' | 'invite'>('users');
  const [users, setUsers] = useState<UserData[]>([]);
  const [loading, setLoading] = useState(false);
  
  // Filter States
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  // Invite States
  const [inviteEmail, setInviteEmail] = useState('');
  const [generatedToken, setGeneratedToken] = useState<InviteResponse | null>(null);
  const [inviteLoading, setInviteLoading] = useState(false);

  // Feedback States
  const [message, setMessage] = useState<{type: 'success' | 'error', text: string} | null>(null);

  useEffect(() => {
    if (activeTab === 'users') {
      fetchUsers();
    }
  }, [activeTab, page, statusFilter]);

  const fetchUsers = async () => {
    setLoading(true);
    try {
      const response: any = await adminService.getUsers(page, search, statusFilter);
      setUsers(response.data.data);
      setTotalPages(response.data.last_page);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setPage(1);
    fetchUsers();
  };

  const handleApprove = async (userId: number) => {
    if (!window.confirm('Are you sure you want to approve this user?')) return;
    
    try {
      await adminService.approveUser(userId);
      setMessage({ type: 'success', text: 'User approved successfully' });
      fetchUsers();
    } catch (error: any) {
      setMessage({ type: 'error', text: error.response?.data?.message || 'Failed to approve user' });
    }
  };

  const handleInvite = async (e: React.FormEvent) => {
    e.preventDefault();
    setInviteLoading(true);
    setGeneratedToken(null);
    setMessage(null);

    try {
      const response: any = await adminService.inviteAdmin(inviteEmail);
      setGeneratedToken(response.data);
      setMessage({ type: 'success', text: 'Invitation token generated!' });
      setInviteEmail('');
    } catch (error: any) {
      setMessage({ type: 'error', text: error.response?.data?.message || 'Failed to generate invitation' });
    } finally {
      setInviteLoading(false);
    }
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    alert('Token copied to clipboard!');
  };

  return (
    <div className="admin-container">
      <div className="admin-header">
        <h1>👥 User Management Center</h1>
        <p>Manage users, approvals, and administrator invitations</p>
      </div>

      {message && (
        <div className={`admin-alert ${message.type}`}>
          {message.type === 'success' ? '✅' : '⚠️'} {message.text}
          <button onClick={() => setMessage(null)} className="close-btn">×</button>
        </div>
      )}

      <div className="admin-tabs">
        <button 
          className={activeTab === 'users' ? 'tab active' : 'tab'}
          onClick={() => setActiveTab('users')}
        >
          User List & Approvals
        </button>
        <button 
          className={activeTab === 'invite' ? 'tab active' : 'tab'}
          onClick={() => setActiveTab('invite')}
        >
          Invite New Admin
        </button>
      </div>

      <div className="admin-content">
        {activeTab === 'users' ? (
          <div className="user-list-section">
            <div className="filters-bar">
              <form onSubmit={handleSearch} className="search-form">
                <input 
                  type="text" 
                  placeholder="Search by name, email, ID..." 
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
                <button type="submit" className="btn-search">Search</button>
              </form>
              
              <select 
                value={statusFilter} 
                onChange={(e) => setStatusFilter(e.target.value)}
                className="status-filter"
              >
                <option value="">All Status</option>
                <option value="pending">Pending Approval</option>
                <option value="active">Active</option>
              </select>
            </div>

            {loading ? (
              <div className="loading-state">Loading users data...</div>
            ) : (
              <div className="table-responsive">
                <table className="admin-table">
                  <thead>
                    <tr>
                      <th>Employee</th>
                      <th>Role</th>
                      <th>Dept</th>
                      <th>Status</th>
                      <th>Joined</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {users.length > 0 ? (
                      users.map((user) => (
                        <tr key={user.id} className={!user.is_active ? 'row-pending' : ''}>
                          <td>
                            <div className="user-info">
                              <span className="user-name">{user.first_name} {user.last_name}</span>
                              <span className="user-email">{user.email}</span>
                              <span className="user-id-badge">{user.employee_id}</span>
                            </div>
                          </td>
                          <td><span className={`role-badge ${user.role.role_code}`}>{user.role.role_name}</span></td>
                          <td>{user.department?.department_name || '-'}</td>
                          <td>
                            {user.is_active ? (
                              <span className="status-badge active">Active</span>
                            ) : (
                              <span className="status-badge pending">Pending Approval</span>
                            )}
                          </td>
                          <td>{new Date(user.created_at).toLocaleDateString()}</td>
                          <td>
                            {!user.is_active && (
                              <button 
                                className="btn-approve"
                                onClick={() => handleApprove(user.id)}
                              >
                                ✓ Approve
                              </button>
                            )}
                          </td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan={6} className="text-center">No users found.</td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            )}
            
            <div className="pagination">
              <button disabled={page === 1} onClick={() => setPage(page - 1)}>Previous</button>
              <span>Page {page} of {totalPages}</span>
              <button disabled={page === totalPages} onClick={() => setPage(page + 1)}>Next</button>
            </div>
          </div>
        ) : (
          <div className="invite-section">
            <div className="invite-card">
              <div className="invite-icon">🛡️</div>
              <h2>Generate Admin Invitation</h2>
              <p>Create a secure, one-time token for new administrators to register.</p>
              
              <form onSubmit={handleInvite} className="invite-form">
                <div className="form-group">
                  <label>New Admin Email</label>
                  <input 
                    type="email" 
                    required 
                    placeholder="colleague@banksumut.co.id"
                    value={inviteEmail}
                    onChange={(e) => setInviteEmail(e.target.value)}
                  />
                </div>
                <button type="submit" className="btn-generate" disabled={inviteLoading}>
                  {inviteLoading ? 'Generating...' : 'Generate Token'}
                </button>
              </form>

              {generatedToken && (
                <div className="token-result">
                  <h4>🎉 Invitation Created!</h4>
                  <div className="token-box">
                    <code>{generatedToken.token}</code>
                    <button onClick={() => copyToClipboard(generatedToken.token)}>Copy</button>
                  </div>
                  <p className="token-expiry">
                    ⚠️ Valid for 24 hours only. Send this token to <strong>{generatedToken.email}</strong> securely.
                  </p>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}