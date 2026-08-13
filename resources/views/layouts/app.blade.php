<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'CyberGuard')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #ffffff;
            min-height: 100vh;
        }
        
        .crimson-header {
            background-color: #DC143C;
            padding: 16px 40px;
            box-shadow: 0 2px 10px rgba(220, 20, 60, 0.3);
        }
        
        .crimson-header .brand {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: 1px;
        }
        
        .crimson-header .brand i {
            margin-right: 10px;
        }
        
        .btn-crimson {
            background-color: #DC143C;
            color: #ffffff;
            border: none;
            padding: 10px 30px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-family: 'Cairo', sans-serif;
        }
        
        .btn-crimson:hover {
            background-color: #b01030;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 20, 60, 0.4);
        }
        
        .btn-crimson-outline {
            background-color: transparent;
            color: #DC143C;
            border: 2px solid #DC143C;
            padding: 8px 24px;
            font-weight: 600;
            font-size: 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-family: 'Cairo', sans-serif;
        }
        
        .btn-crimson-outline:hover {
            background-color: #DC143C;
            color: #ffffff;
        }
        
        .tagline-section {
            padding: 40px 20px 20px 20px;
            text-align: center;
        }
        
        .tagline-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        
        .tagline-section .subtitle {
            font-size: 16px;
            color: #555555;
            max-width: 700px;
            margin: 0 auto 20px auto;
            line-height: 1.6;
        }
        
        .tagline-section .action-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .tutorial-box {
            background-color: #D9D9D9;
            padding: 30px 40px;
            border-radius: 12px;
            margin: 20px auto;
            max-width: 1100px;
        }
        
        .tutorial-box .row {
            gap: 20px 0;
        }
        
        .tutorial-step {
            background-color: #ffffff;
            padding: 20px 18px;
            border-radius: 8px;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #DC143C;
            transition: transform 0.2s ease;
        }
        
        .tutorial-step:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .tutorial-step .step-number {
            font-weight: 700;
            color: #DC143C;
            font-size: 14px;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .tutorial-step .step-text {
            font-size: 14px;
            color: #333333;
            line-height: 1.5;
            font-weight: 400;
        }
        
        .view-timeline-section {
            text-align: center;
            padding: 25px 20px 50px 20px;
        }
        
        .view-timeline-section .input-group {
            max-width: 550px;
            margin: 0 auto;
        }
        
        .view-timeline-section .input-group input {
            font-family: 'Cairo', sans-serif;
            padding: 10px 16px;
            border-radius: 6px 0 0 6px;
            border: 1px solid #d0d0d0;
        }
        
        .view-timeline-section .input-group input:focus {
            border-color: #DC143C;
            box-shadow: 0 0 0 0.2rem rgba(220, 20, 60, 0.15);
        }
        
        .view-timeline-section .input-group .btn {
            border-radius: 0 6px 6px 0;
        }
        
        .footer-note {
            text-align: center;
            padding: 20px;
            color: #888888;
            font-size: 13px;
            border-top: 1px solid #eeeeee;
            margin-top: 20px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .crimson-header {
                padding: 12px 20px;
            }
            
            .crimson-header .brand {
                font-size: 20px;
            }
            
            .tagline-section h1 {
                font-size: 24px;
            }
            
            .tagline-section .subtitle {
                font-size: 14px;
                padding: 0 10px;
            }
            
            .tutorial-box {
                padding: 20px 16px;
                margin: 15px 10px;
            }
            
            .tutorial-step {
                padding: 16px 14px;
            }
            
            .tutorial-step .step-text {
                font-size: 13px;
            }
            
            .view-timeline-section .input-group {
                max-width: 100%;
                padding: 0 10px;
            }
            
            .tagline-section .action-row {
                flex-direction: column;
                gap: 12px;
            }
        }
        
        @media (max-width: 576px) {
            .tutorial-box .row > div {
                margin-bottom: 12px;
            }
            
            .tutorial-box .row > div:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Crimson Header -->
    <header class="crimson-header">
        <a href="{{ url('/') }}" class="brand">
            <i class="fas fa-shield-alt"></i> CyberGuard
        </a>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>