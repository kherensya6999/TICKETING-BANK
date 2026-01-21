import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchTicket } from '../store/slices/ticketSlice';
import { ticketService } from '../services/ticketService';
import './TicketDetail.css';

export default function TicketDetail() {
  const { id } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { currentTicket, loading } = useAppSelector((state) => state.tickets);
  const [commentText, setCommentText] = useState('');
  const [addingComment, setAddingComment] = useState(false);

  useEffect(() => {
    if (id) {
      dispatch(fetchTicket(parseInt(id)));
    }
  }, [id, dispatch]);

  const handleAddComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!commentText.trim() || !id) return;

    setAddingComment(true);
    try {
      await ticketService.addComment(parseInt(id), {
        comment_text: commentText,
        comment_type: 'PUBLIC',
      });
      setCommentText('');
      dispatch(fetchTicket(parseInt(id)));
    } catch (error) {
      console.error('Failed to add comment:', error);
    } finally {
      setAddingComment(false);
    }
  };

  if (loading || !currentTicket) {
    return (
      <div className="loading-state">
        <div className="spinner-large"></div>
        <p>Loading ticket details...</p>
      </div>
    );
  }

  const ticket: any = currentTicket;

  return (
    <div className="ticket-detail-page">
      <div className="ticket-header">
        <div className="ticket-header-left">
          <button onClick={() => navigate('/tickets')} className="btn-back">
            ← Back to Tickets
          </button>
          <div className="ticket-title-section">
            <h1>{ticket.subject}</h1>
            <div className="ticket-meta">
              <span className="ticket-number">{ticket.ticket_number}</span>
              <span className={`badge status-${ticket.status?.toLowerCase().replace('_', '-')}`}>
                {ticket.status}
              </span>
              <span className={`badge priority-${ticket.priority?.toLowerCase()}`}>
                {ticket.priority}
              </span>
              {ticket.is_security_incident && (
                <span className="badge badge-danger">🔒 Security Incident</span>
              )}
            </div>
          </div>
        </div>
      </div>

      <div className="ticket-content-grid">
        <div className="ticket-main">
          <div className="ticket-section">
            <h3>Description</h3>
            <div className="ticket-description">
              {ticket.description}
            </div>
          </div>

          <div className="ticket-section">
            <h3>Details</h3>
            <div className="details-grid">
              <div className="detail-item">
                <span className="detail-label">Category</span>
                <span className="detail-value">
                  {ticket.category?.category_name || 'N/A'}
                </span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Requester</span>
                <span className="detail-value">
                  {ticket.requester?.full_name || ticket.requester?.employee_id || 'N/A'}
                </span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Assigned To</span>
                <span className="detail-value">
                  {ticket.assigned_to?.full_name || 'Unassigned'}
                </span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Created</span>
                <span className="detail-value">
                  {new Date(ticket.created_at).toLocaleString()}
                </span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Due Date</span>
                <span className="detail-value">
                  {ticket.due_date
                    ? new Date(ticket.due_date).toLocaleString()
                    : 'N/A'}
                </span>
              </div>
            </div>
          </div>

          <div className="ticket-section">
            <h3>Comments</h3>
            <div className="comments-list">
              {(ticket.comments && ticket.comments.length > 0) ? (
                ticket.comments.map((comment: any) => (
                  <div key={comment.id} className="comment-item">
                    <div className="comment-header">
                      <div className="comment-author">
                        <div className="comment-avatar">
                          {comment.user?.full_name?.charAt(0) || 'U'}
                        </div>
                        <div>
                          <div className="comment-author-name">
                            {comment.user?.full_name || 'User'}
                          </div>
                          <div className="comment-date">
                            {new Date(comment.created_at).toLocaleString()}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className="comment-text">{comment.comment_text}</div>
                  </div>
                ))
              ) : (
                <div className="empty-comments">No comments yet</div>
              )}
            </div>

            <form onSubmit={handleAddComment} className="comment-form">
              <textarea
                value={commentText}
                onChange={(e) => setCommentText(e.target.value)}
                placeholder="Add a comment..."
                rows={4}
                className="comment-input"
                required
                disabled={addingComment}
              />
              <button
                type="submit"
                className="btn btn-primary"
                disabled={addingComment || !commentText.trim()}
              >
                {addingComment ? 'Posting...' : 'Post Comment'}
              </button>
            </form>
          </div>
        </div>

        <div className="ticket-sidebar">
          <div className="sidebar-card">
            <h4>Quick Actions</h4>
            <div className="action-buttons">
              {ticket.status !== 'RESOLVED' && (
                <button className="btn btn-success btn-block">
                  ✅ Resolve Ticket
                </button>
              )}
              <button className="btn btn-outline btn-block">
                📧 Notify Team
              </button>
            </div>
          </div>

          <div className="sidebar-card">
            <h4>Timeline</h4>
            <div className="timeline">
              <div className="timeline-item">
                <div className="timeline-dot"></div>
                <div className="timeline-content">
                  <div className="timeline-title">Ticket Created</div>
                  <div className="timeline-date">
                    {new Date(ticket.created_at).toLocaleString()}
                  </div>
                </div>
              </div>
              {ticket.first_response_at && (
                <div className="timeline-item">
                  <div className="timeline-dot"></div>
                  <div className="timeline-content">
                    <div className="timeline-title">First Response</div>
                    <div className="timeline-date">
                      {new Date(ticket.first_response_at).toLocaleString()}
                    </div>
                  </div>
                </div>
              )}
              {ticket.resolved_at && (
                <div className="timeline-item">
                  <div className="timeline-dot"></div>
                  <div className="timeline-content">
                    <div className="timeline-title">Resolved</div>
                    <div className="timeline-date">
                      {new Date(ticket.resolved_at).toLocaleString()}
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
