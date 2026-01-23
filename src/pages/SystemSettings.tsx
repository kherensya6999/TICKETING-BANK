import React, { useState } from 'react';
import './SystemSettings.css';

export default function SystemSettings() {
  const [settings, setSettings] = useState({ siteName: 'IT Security System', maintenance: false });

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    alert('Pengaturan disimpan!');
  };

  return (
    <div className="settings-container">
      <h1>⚙️ System Configuration</h1>
      <div className="settings-card">
        <form onSubmit={handleSave}>
          <div className="form-group">
            <label>Application Name</label>
            <input type="text" value={settings.siteName} onChange={e => setSettings({...settings, siteName: e.target.value})} />
          </div>
          <div className="form-group">
            <label>
              <input type="checkbox" checked={settings.maintenance} onChange={e => setSettings({...settings, maintenance: e.target.checked})} />
              Maintenance Mode
            </label>
          </div>
          <button type="submit" className="btn-save">Simpan</button>
        </form>
      </div>
    </div>
  );
}