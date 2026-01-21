<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Security Ticketing System - Bank Sumut</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #003366 0%, #004d99 50%, #00a651 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .background-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }
        .shape-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
        }
        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            right: -100px;
            animation-delay: 5s;
        }
        .shape-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }
        .container {
            text-align: center;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            position: relative;
            z-index: 10;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .logo-section {
            margin-bottom: 2rem;
        }
        .logo-circle {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            font-weight: 700;
        }
        .subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            font-weight: 300;
        }
        .status-card {
            background: rgba(76, 175, 80, 0.2);
            padding: 1.5rem;
            border-radius: 16px;
            margin: 2rem 0;
            border: 2px solid rgba(76, 175, 80, 0.4);
            backdrop-filter: blur(10px);
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: 0.5rem;
        }
        .status-text {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .status-detail {
            font-size: 0.95rem;
            opacity: 0.9;
        }
        .links-section {
            margin-top: 2.5rem;
        }
        .links-title {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
            font-weight: 600;
        }
        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .link-card {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .link-card:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .link-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            display: block;
        }
        .link-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        .link-desc {
            font-size: 0.85rem;
            opacity: 0.8;
        }
        .info-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        .info-title {
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            opacity: 0.8;
        }
        .info-value {
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }
        .footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.85rem;
            opacity: 0.8;
        }
        .primary-link {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .primary-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="container">
        <div class="logo-section">
            <div class="logo-circle">🏦</div>
            <h1>Bank Sumut</h1>
            <p class="subtitle">IT Security Ticketing System</p>
        </div>

        <div class="status-card">
            <div class="status-icon">✅</div>
            <div class="status-text">Backend API Running</div>
            <div class="status-detail">Laravel Framework 10.50.0</div>
        </div>

        <div class="links-section">
            <div class="links-title">🚀 Akses Aplikasi</div>
            <div class="links-grid">
                <a href="http://localhost:3000" target="_blank" class="link-card">
                    <span class="link-icon">💻</span>
                    <div class="link-title">Web Application</div>
                    <div class="link-desc">Aplikasi Ticketing System</div>
                </a>
                <a href="/api" class="link-card">
                    <span class="link-icon">📡</span>
                    <div class="link-title">API Endpoints</div>
                    <div class="link-desc">REST API Documentation</div>
                </a>
            </div>

            <a href="http://localhost:3000" target="_blank" class="primary-link">
                🎯 Buka Aplikasi Ticketing System →
            </a>
        </div>

        <div class="info-section">
            <div class="info-title">📋 System Information</div>
            <div class="info-item">
                <span class="info-label">Backend URL:</span>
                <span class="info-value">http://127.0.0.1:8000</span>
            </div>
            <div class="info-item">
                <span class="info-label">Frontend URL:</span>
                <span class="info-value">http://localhost:3000</span>
            </div>
            <div class="info-item">
                <span class="info-label">API Base:</span>
                <span class="info-value">/api</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value" style="color: #4caf50;">● Online</span>
            </div>
        </div>

        <div class="footer">
            <p>© 2026 Bank Sumut. All rights reserved.</p>
            <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                Enterprise IT Service Management Solution
            </p>
        </div>
    </div>
</body>
</html>
