<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>BimCall — @yield('title', 'Accueil')</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
            color: #1a1a2e; 
            line-height: 1.5;
            min-height: 100vh;
        }

        /* NAVIGATION RESPONSIVE */
        nav { 
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff; 
            padding: 0 5%; 
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            min-height: 64px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
            gap: 0;
            flex-wrap: wrap;
        }
        
        nav .brand { 
            font-weight: 800; 
            font-size: 20px; 
            margin-right: 32px; 
            background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.3px;
        }
        
        nav a { 
            color: #cbd5e1; 
            text-decoration: none; 
            padding: 20px 16px; 
            font-size: 14px; 
            font-weight: 500;
            border-bottom: 3px solid transparent; 
            transition: all 0.25s ease;
            position: relative;
        }
        
        nav a:hover { 
            color: #fff; 
            background: rgba(124, 58, 237, 0.1);
        }
        
        nav a.active { 
            color: #fff; 
            border-bottom-color: #7c3aed; 
        }

        /* LAYOUT RESPONSIVE */
        .container { 
            max-width: 1280px; 
            margin: 0 auto; 
            padding: 32px 5%; 
        }
        
        h1 { 
            font-size: clamp(20px, 5vw, 28px); 
            font-weight: 800; 
            margin-bottom: 24px; 
            background: linear-gradient(135deg, #1a1a2e 0%, #4a4a6a 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        h2 { 
            font-size: clamp(14px, 4vw, 18px); 
            font-weight: 700; 
            margin-bottom: 16px; 
            color: #334155; 
            letter-spacing: -0.2px;
        }

        /* CARDS RESPONSIVE */
        .card { 
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px; 
            padding: clamp(16px, 4vw, 24px); 
            box-shadow: 0 4px 20px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            margin-bottom: 24px; 
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid rgba(226, 232, 240, 0.6);
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .stat-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            gap: 20px; 
            margin-bottom: 24px; 
        }
        
        .stat-card { 
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px; 
            padding: 20px 16px; 
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            text-align: center; 
            transition: all 0.3s ease;
            border: 1px solid rgba(124, 58, 237, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #7c3aed 0%, #a78bfa 100%);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.15);
        }
        
        .stat-card .value { 
            font-size: clamp(28px, 6vw, 42px); 
            font-weight: 800; 
            background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.2;
        }
        
        .stat-card .label { 
            font-size: 13px; 
            color: #64748b; 
            margin-top: 8px; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ALERTS RESPONSIVE */
        .alert { 
            padding: 14px 18px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            font-size: 14px; 
            font-weight: 500;
            backdrop-filter: blur(10px);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success { 
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46; 
            border-left: 4px solid #10b981; 
        }
        
        .alert-error   { 
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b; 
            border-left: 4px solid #ef4444; 
        }
        
        .alert-warning { 
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e; 
            border-left: 4px solid #f59e0b; 
        }
        
        .alert-info    { 
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af; 
            border-left: 4px solid #3b82f6; 
        }

        /* FORMS RESPONSIVE */
        .form-row { 
            display: flex; 
            gap: 16px; 
            align-items: flex-end; 
            flex-wrap: wrap; 
        }
        
        input[type=text], 
        input[type=file], 
        input[type=email],
        input[type=tel],
        input[type=number],
        select, 
        textarea {
            border: 2px solid #e2e8f0; 
            border-radius: 10px; 
            padding: 11px 14px;
            font-size: 14px; 
            width: 100%; 
            outline: none; 
            transition: all 0.25s ease;
            font-family: inherit;
            background: #fff;
        }
        
        input:focus, 
        select:focus, 
        textarea:focus { 
            border-color: #7c3aed; 
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        
        label { 
            display: block; 
            font-size: 12px; 
            font-weight: 700; 
            color: #475569; 
            margin-bottom: 6px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        
        .field { 
            flex: 1; 
            min-width: 180px; 
        }

        /* BUTTONS RESPONSIVE */
        .btn { 
            padding: 11px 22px; 
            border-radius: 10px; 
            border: none; 
            font-size: 14px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.25s ease; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px;
            letter-spacing: 0.3px;
        }
        
        .btn-primary  { 
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: #fff; 
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
        }
        
        .btn-primary:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
        }
        
        .btn-green    { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff; 
        }
        
        .btn-green:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .btn-red      { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff; 
        }
        
        .btn-red:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .btn-gray     { 
            background: #f1f5f9; 
            color: #475569; 
            border: 1px solid #e2e8f0;
        }
        
        .btn-gray:hover { 
            background: #e2e8f0; 
            transform: translateY(-1px);
        }
        
        .btn-sm { 
            padding: 6px 12px; 
            font-size: 12px; 
        }
        
        .btn:active {
            transform: translateY(0);
        }

        /* TABLE RESPONSIVE */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 14px; 
            min-width: 500px;
        }
        
        th { 
            text-align: left; 
            padding: 14px 12px; 
            color: #475569; 
            font-size: 12px; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            border-bottom: 2px solid #e2e8f0; 
            background: #f8fafc;
        }
        
        td { 
            padding: 14px 12px; 
            border-bottom: 1px solid #f1f5f9; 
        }
        
        tr:hover td { 
            background: #f8fafc; 
        }

        /* STATUS BADGES */
        .badge { 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 700; 
            display: inline-block;
            letter-spacing: 0.3px;
        }
        
        .badge-pending  { 
            background: #fef3c7; 
            color: #92400e; 
            border: 1px solid #fde68a;
        }
        
        .badge-calling  { 
            background: #dbeafe; 
            color: #1e40af; 
            border: 1px solid #bfdbfe;
        }
        
        .badge-done     { 
            background: #d1fae5; 
            color: #065f46; 
            border: 1px solid #a7f3d0;
        }
        
        .badge-failed   { 
            background: #fee2e2; 
            color: #991b1b; 
            border: 1px solid #fecaca;
        }

        /* LIVE INDICATOR */
        .live-dot { 
            display: inline-block; 
            width: 10px; 
            height: 10px; 
            background: #10b981; 
            border-radius: 50%; 
            animation: pulse 1.5s infinite;
            box-shadow: 0 0 6px rgba(16, 185, 129, 0.6);
        }
        
        @keyframes pulse { 
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                padding: 12px 5%;
            }
            
            .nav-left {
                width: 100%;
                justify-content: center;
            }
            
            nav .brand {
                margin-right: 0;
                margin-bottom: 8px;
                text-align: center;
                width: 100%;
            }
            
            nav a {
                padding: 12px 12px;
                font-size: 13px;
            }
            
            .stat-grid {
                gap: 12px;
            }
            
            .form-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .field {
                min-width: auto;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 20px 4%;
            }
            
            .card {
                padding: 16px;
            }
            
            .stat-card .value {
                font-size: 28px;
            }
            
            nav a {
                padding: 8px 10px;
                font-size: 12px;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 12px;
            }
            
            .badge {
                font-size: 10px;
                padding: 3px 8px;
            }
        }
        
        @media (min-width: 1920px) {
            .container {
                max-width: 1400px;
            }
            
            .stat-grid {
                gap: 28px;
            }
        }

        /* SCROLLBAR STYLING */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* UTILITY CLASSES */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-2 { margin-top: 8px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-3 { margin-top: 16px; }
        .mb-3 { margin-bottom: 16px; }
        
        /* LOADING STATE */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-left">
        <span class="brand">📞 BimCall</span>
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('contacts.index') }}" class="{{ request()->routeIs('contacts.*') ? 'active' : '' }}">Contacts</a>
        <a href="{{ route('calls.logs') }}" class="{{ request()->routeIs('calls.logs') ? 'active' : '' }}">Historique</a>
        <a href="{{ route('prompts.index') }}" class="{{ request()->routeIs('prompts.*') ? 'active' : '' }}">Offres / IA</a>
    </div>
</nav>

<div class="container">
    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">⚠️ {{ session('warning') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">ℹ️ {{ session('info') }}</div>
    @endif

    @yield('content')
</div>

@yield('scripts')

<script>
    // Add loading state to buttons when clicked
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.type === 'submit' || this.classList.contains('btn-primary')) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '⏳ Chargement...';
                    this.classList.add('loading');
                    
                    // Reset after 5 seconds (in case of error)
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('loading');
                    }, 5000);
                }
            });
        });
    });
</script>
</body>
</html>