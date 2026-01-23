import { useState } from 'react';
import './Reports.css';

export default function Reports() {
  const [dateRange, setDateRange] = useState('month');
  const [isExporting, setIsExporting] = useState(false);

  const stats = {
    total: 120, resolved: 98, sla_breached: 5, avg_resolution_time: '4.5 Hours',
    categories: [
      { name: 'Network', count: 45, color: '#1976d2' },
      { name: 'Hardware', count: 30, color: '#388e3c' },
      { name: 'Software', count: 25, color: '#fbc02d' },
      { name: 'Security', count: 20, color: '#d32f2f' },
    ]
  };

  const handleExport = () => {
    setIsExporting(true);
    setTimeout(() => { alert('Laporan berhasil diunduh!'); setIsExporting(false); }, 1000);
  };

  return (
    <div className="reports-container">
      <div className="reports-header">
        <h1>📈 Executive Reports</h1>
        <button className="btn-export" onClick={handleExport} disabled={isExporting}>
          {isExporting ? 'Downloading...' : '📥 Export PDF'}
        </button>
      </div>
      <div className="report-summary">
        {/* Simple Cards */}
        <div className="summary-card"><h3>Total</h3><div className="value">{stats.total}</div></div>
        <div className="summary-card"><h3>Resolved</h3><div className="value">{stats.resolved}</div></div>
      </div>
      <div className="chart-section">
        <h2>Distribusi Kategori</h2>
        {/* Simple Bar Visualization */}
        {stats.categories.map(cat => (
          <div key={cat.name} style={{marginBottom: '10px'}}>
            <div style={{display: 'flex', justifyContent: 'space-between'}}><span>{cat.name}</span><span>{cat.count}</span></div>
            <div style={{height: '10px', background: '#eee', borderRadius: '5px'}}>
              <div style={{width: `${(cat.count/stats.total)*100}%`, background: cat.color, height: '100%', borderRadius: '5px'}}></div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}