<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Connexion — Bimcall</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #4c1d95 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background particles effect */
        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(124, 58, 237, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        body::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 80% 80%, rgba(124, 58, 237, 0.05) 0%, transparent 60%);
            pointer-events: none;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: clamp(28px, 6vw, 42px) clamp(24px, 5vw, 36px);
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            text-align: center;
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            font-size: 48px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
        }
        
        .logo i {
            font-size: 48px;
        }
        
        h1 { 
            font-size: 22px; 
            font-weight: 800; 
            background: linear-gradient(135deg, #1a1a2e 0%, #4c1d95 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 6px; 
        }
        
        .subtitle { 
            font-size: 13px; 
            color: #64748b; 
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .subtitle i {
            font-size: 12px;
            color: #7c3aed;
        }

        /* Form styles */
        label { 
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px; 
            font-weight: 700; 
            color: #475569; 
            margin-bottom: 8px; 
            text-transform: uppercase; 
            letter-spacing: 0.05em; 
            text-align: left; 
        }
        
        label i {
            font-size: 11px;
            color: #7c3aed;
        }
        
        input[type=text] {
            width: 100%;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 13px 16px;
            font-size: 24px;
            outline: none;
            transition: all 0.25s ease;
            text-align: center;
            letter-spacing: 0.3em;
            font-weight: 700;
            font-family: 'SF Mono', 'Monaco', 'Cascadia Code', monospace;
            background: #f8fafc;
        }
        
        input[type=text]:focus { 
            border-color: #7c3aed; 
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
            background: #fff;
        }
        
        input[type=text]::placeholder {
            letter-spacing: normal;
            font-size: 14px;
            font-weight: normal;
            color: #cbd5e1;
        }

        /* Button styles */
        .btn { 
            width: 100%; 
            padding: 13px 16px; 
            border-radius: 12px; 
            border: none; 
            font-size: 15px; 
            font-weight: 700; 
            cursor: pointer; 
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: #fff; 
            margin-top: 24px; 
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .btn i {
            font-size: 14px;
            transition: transform 0.2s ease;
        }
        
        .btn:hover { 
            background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.4);
        }
        
        .btn:hover i {
            transform: translateX(3px);
        }
        
        .btn:active {
            transform: translateY(0);
        }

        /* Alert styles */
        .alert { 
            padding: 12px 16px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            font-size: 13px; 
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .alert i {
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .alert-error { 
            background: #fee2e2; 
            color: #991b1b; 
            border-left: 4px solid #ef4444; 
        }
        
        .alert-success { 
            background: #d1fae5; 
            color: #065f46; 
            border-left: 4px solid #10b981; 
        }
        
        .alert-info { 
            background: #dbeafe; 
            color: #1e40af; 
            border-left: 4px solid #3b82f6; 
        }

        /* Responsive design */
        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            
            .login-card {
                padding: 28px 20px;
                border-radius: 20px;
            }
            
            .logo {
                font-size: 40px;
            }
            
            .logo i {
                font-size: 40px;
            }
            
            h1 {
                font-size: 20px;
            }
            
            .subtitle {
                font-size: 12px;
                margin-bottom: 24px;
            }
            
            input[type=text] {
                padding: 11px 14px;
                font-size: 20px;
                letter-spacing: 0.25em;
            }
            
            .btn {
                padding: 11px 14px;
                font-size: 14px;
                margin-top: 20px;
            }
            
            .alert {
                padding: 10px 14px;
                font-size: 12px;
                margin-bottom: 16px;
            }
            
            .alert i {
                font-size: 13px;
            }
        }
        
        @media (max-width: 380px) {
            .login-card {
                padding: 24px 16px;
            }
            
            input[type=text] {
                font-size: 18px;
                letter-spacing: 0.2em;
            }
            
            .logo i {
                font-size: 36px;
            }
        }
        
        @media (min-width: 1200px) {
            .login-card {
                max-width: 440px;
            }
        }
        
        /* Loading state for button */
        .btn.loading {
            opacity: 0.7;
            cursor: wait;
        }
        
        .btn.loading i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Accessibility focus styles */
        .btn:focus-visible,
        input:focus-visible {
            outline: 2px solid #7c3aed;
            outline-offset: 2px;
        }
        
        /* Smooth transitions for all interactive elements */
        * {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="fas fa-phone-alt"></i>
        </div>
        <h1>Bimcall</h1>
        <p class="subtitle">
            <i class="fas fa-key"></i> Entrez le code d'accès
        </p>

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first('code') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                {{ session('info') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST" id="login-form">
            @csrf
            <label>
                <i class="fas fa-lock"></i> Code d'accès
            </label>
            <input type="text" 
                   name="code" 
                   placeholder="••••" 
                   maxlength="10" 
                   autocomplete="off" 
                   required 
                   autofocus
                   id="code-input">

            <button type="submit" class="btn" id="login-btn">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
    </div>

    <script>
        // Add loading state on form submission
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const btn = document.getElementById('login-btn');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';
            btn.classList.add('loading');
            btn.disabled = true;
            
            // Re-enable button if something goes wrong (timeout)
            setTimeout(() => {
                if (btn.disabled) {
                    btn.innerHTML = originalContent;
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            }, 10000);
        });
        
        // Optional: Auto-focus and select input
        const codeInput = document.getElementById('code-input');
        if (codeInput) {
            codeInput.focus();
        }
    </script>
</body>
</html>