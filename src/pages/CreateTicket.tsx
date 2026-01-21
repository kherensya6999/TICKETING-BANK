import { useState, useEffect } from 'react';
import { useAppDispatch } from '../store/hooks';
import { useNavigate } from 'react-router-dom';
import { createTicket } from '../store/slices/ticketSlice';
import { categoryService } from '../services/categoryService';
import './CreateTicket.css';

export default function CreateTicket() {
  const [formData, setFormData] = useState({
    category_id: '',
    subcategory_id: '',
    priority: 'MEDIUM',
    subject: '',
    description: '',
    attachments: [] as File[],
  });
  const [categories, setCategories] = useState([]);
  const [subcategories, setSubcategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const dispatch = useAppDispatch();
  const navigate = useNavigate();

  useEffect(() => {
    categoryService.getCategories().then((res) => {
      setCategories(res.data.data);
    });
  }, []);

  useEffect(() => {
    if (formData.category_id) {
      categoryService.getSubcategories(parseInt(formData.category_id)).then((res) => {
        setSubcategories(res.data.data);
      });
    } else {
      setSubcategories([]);
    }
  }, [formData.category_id]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const result: any = await dispatch(createTicket(formData)).unwrap();
      navigate(`/tickets/${result.ticket_id || result.id}`);
    } catch (err: any) {
      setError(err.message || 'Failed to create ticket');
    } finally {
      setLoading(false);
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      setFormData({
        ...formData,
        attachments: Array.from(e.target.files),
      });
    }
  };

  return (
    <div className="create-ticket-page">
      <div className="create-ticket-card">
        <div className="card-header">
          <h2>Create New Ticket</h2>
          <p>Fill in the details below to create a new support ticket</p>
        </div>

        <form onSubmit={handleSubmit} className="ticket-form">
          <div className="form-row">
            <div className="form-group">
              <label htmlFor="category">Category *</label>
              <select
                id="category"
                value={formData.category_id}
                onChange={(e) =>
                  setFormData({
                    ...formData,
                    category_id: e.target.value,
                    subcategory_id: '',
                  })
                }
                required
                className="form-select"
                disabled={loading}
              >
                <option value="">Select Category</option>
                {categories.map((cat: any) => (
                  <option key={cat.id} value={cat.id}>
                    {cat.category_name}
                  </option>
                ))}
              </select>
            </div>

            {formData.category_id && (
              <div className="form-group">
                <label htmlFor="subcategory">Subcategory</label>
                <select
                  id="subcategory"
                  value={formData.subcategory_id}
                  onChange={(e) =>
                    setFormData({ ...formData, subcategory_id: e.target.value })
                  }
                  className="form-select"
                  disabled={loading}
                >
                  <option value="">Select Subcategory</option>
                  {subcategories.map((sub: any) => (
                    <option key={sub.id} value={sub.id}>
                      {sub.subcategory_name}
                    </option>
                  ))}
                </select>
              </div>
            )}
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="priority">Priority *</label>
              <select
                id="priority"
                value={formData.priority}
                onChange={(e) =>
                  setFormData({ ...formData, priority: e.target.value })
                }
                required
                className="form-select"
                disabled={loading}
              >
                <option value="LOW">Low</option>
                <option value="MEDIUM">Medium</option>
                <option value="HIGH">High</option>
                <option value="URGENT">Urgent</option>
                <option value="CRITICAL">Critical</option>
              </select>
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="subject">Subject *</label>
            <input
              id="subject"
              type="text"
              value={formData.subject}
              onChange={(e) =>
                setFormData({ ...formData, subject: e.target.value })
              }
              required
              placeholder="Brief description of the issue"
              className="form-input"
              disabled={loading}
            />
          </div>

          <div className="form-group">
            <label htmlFor="description">Description *</label>
            <textarea
              id="description"
              value={formData.description}
              onChange={(e) =>
                setFormData({ ...formData, description: e.target.value })
              }
              required
              rows={8}
              placeholder="Provide detailed information about the issue..."
              className="form-textarea"
              disabled={loading}
            />
          </div>

          <div className="form-group">
            <label htmlFor="attachments">Attachments</label>
            <input
              id="attachments"
              type="file"
              multiple
              onChange={handleFileChange}
              className="form-file"
              disabled={loading}
            />
            {formData.attachments.length > 0 && (
              <div className="file-list">
                {formData.attachments.map((file, index) => (
                  <div key={index} className="file-item">
                    <span>📎</span>
                    <span>{file.name}</span>
                    <span className="file-size">
                      {(file.size / 1024).toFixed(2)} KB
                    </span>
                  </div>
                ))}
              </div>
            )}
          </div>

          {error && (
            <div className="alert alert-error">
              <span>⚠️</span>
              <span>{error}</span>
            </div>
          )}

          <div className="form-actions">
            <button
              type="button"
              onClick={() => navigate('/tickets')}
              className="btn btn-outline"
              disabled={loading}
            >
              Cancel
            </button>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={loading}
            >
              {loading ? (
                <>
                  <span className="spinner"></span>
                  Creating...
                </>
              ) : (
                <>
                  <span>✅</span>
                  Create Ticket
                </>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
