<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'School Management System') }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                background-color: #FDFDFC;
                color: #1b1b18;
                display: flex;
                padding: 1.5rem;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                flex-direction: column;
            }
            
            @media (min-width: 768px) {
                body {
                    padding: 2rem;
                }
            }
            
            .header-nav {
                width: 100%;
                max-width: 335px;
                margin-bottom: 1.5rem;
                display: flex;
                justify-content: flex-end;
                gap: 1rem;
            }
            
            @media (min-width: 1024px) {
                .header-nav {
                    max-width: 64rem;
                }
            }
            
            .btn {
                display: inline-block;
                padding: 0.375rem 1.25rem;
                border-radius: 0.125rem;
                font-size: 0.875rem;
                line-height: 1.25rem;
                text-decoration: none;
                transition: all 0.2s;
            }
            
            .btn-outline {
                border: 1px solid #19140035;
                color: #1b1b18;
            }
            
            .btn-outline:hover {
                border-color: #1915014a;
            }
            
            .main-content {
                display: flex;
                width: 100%;
                flex-direction: column-reverse;
            }
            
            @media (min-width: 1024px) {
                .main-content {
                    flex-direction: row;
                    max-width: 64rem;
                }
            }
            
            .info-panel {
                flex: 1;
                padding: 1.5rem;
                background: white;
                box-shadow: inset 0px 0px 0px 1px rgba(26,26,0,0.16);
                border-bottom-left-radius: 0.5rem;
                border-bottom-right-radius: 0.5rem;
            }
            
            @media (min-width: 1024px) {
                .info-panel {
                    padding: 3rem;
                    border-top-left-radius: 0.5rem;
                    border-bottom-right-radius: 0;
                }
            }
            
            .graphic-panel {
                background-color: #fff2f2;
                position: relative;
                border-top-left-radius: 0.5rem;
                border-top-right-radius: 0.5rem;
                width: 100%;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
            
            @media (min-width: 1024px) {
                .graphic-panel {
                    width: 438px;
                    border-top-left-radius: 0;
                    border-top-right-radius: 0.5rem;
                    border-bottom-right-radius: 0.5rem;
                    margin-left: -1px;
                    margin-bottom: 0;
                }
            }
            
            h1 {
                font-size: 1.5rem;
                font-weight: 500;
                margin-bottom: 0.5rem;
            }
            
            .subtitle {
                color: #706f6c;
                margin-bottom: 1rem;
                font-size: 0.875rem;
                line-height: 1.5;
            }
            
            .btn-primary {
                display: inline-block;
                padding: 0.5rem 1.5rem;
                background-color: #1b1b18;
                color: white;
                border-radius: 0.25rem;
                text-decoration: none;
                margin-top: 1rem;
                transition: all 0.2s;
            }
            
            .btn-primary:hover {
                background-color: black;
            }
            
            .footer-text {
                margin-top: 1.5rem;
                color: #706f6c;
                font-size: 0.75rem;
            }
            
            .school-icon {
                width: 8rem;
                height: 8rem;
                margin: 0 auto;
                color: #f53003;
            }
            
            .graphic-panel h2 {
                margin-top: 1rem;
                font-size: 1.25rem;
                font-weight: 600;
            }
            
            .graphic-panel p {
                margin-top: 0.5rem;
                font-size: 0.875rem;
            }
            
            @media (prefers-color-scheme: dark) {
                body {
                    background-color: #0a0a0a;
                }
                .info-panel {
                    background: #161615;
                    color: #EDEDEC;
                }
                .subtitle {
                    color: #A1A09A;
                }
                .btn-primary {
                    background-color: #eeeeec;
                    color: #1C1C1A;
                }
                .btn-primary:hover {
                    background-color: white;
                }
                .footer-text {
                    color: #A1A09A;
                }
                .school-icon {
                    color: #f61500;
                }
                .graphic-panel {
                    background-color: #1D0002;
                }
                .btn-outline {
                    border-color: #3E3E3A;
                    color: #EDEDEC;
                }
                .btn-outline:hover {
                    border-color: #62605b;
                }
            }
        </style>
    </head>
    <body>
        <div class="header-nav">
            @auth
                <a href="{{ url('/admin') }}" class="btn btn-outline">Dashboard</a>
            @else
                <a href="{{ url('/admin/login') }}" class="btn btn-outline">Staff/Student Login</a>
            @endauth
        </div>
        
        <div class="main-content">
            <div class="info-panel">
                <h1>Welcome</h1>
                <p class="subtitle">
                    School Management System - Manage students, fees, attendance, and more efficiently.
                </p>
                
                <div>
                    <a href="{{ url('/admin/login') }}" class="btn-primary">
                        Login to Dashboard
                    </a>
                </div>
                
                <p class="footer-text">
                    v{{ app()->version() }} | School Management System
                </p>
            </div>
            
            <div class="graphic-panel">
                <div>
                    <svg class="school-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                    <h2>School Management</h2>
                    <p>Efficient. Secure. Reliable.</p>
                </div>
            </div>
        </div>
    </body>
</html>