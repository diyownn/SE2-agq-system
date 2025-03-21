<?php

$errorcode = $_GET['error'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access - Logistics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <style>
        @font-face {
            font-family: 'IBM Plex Sans';
            src: url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap') format('woff2');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'IBM Plex Sans';
            src: url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap') format('woff2');
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: 'IBM Plex Sans';
            src: url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap') format('woff2');
            font-weight: 600;
            font-style: normal;
        }

        @font-face {
            font-family: 'IBM Plex Mono';
            src: url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');
            font-weight: 400;
            font-style: normal;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color:rgb(249, 255, 239);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            color: #1d364d;
        }

        .container {
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            width: 90%;
            max-width: 480px;
            text-align: left;
            position: relative;
            animation: fadeIn 0.5s ease-out forwards;
            border-left: 4px solid rgb(148, 174, 94);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .tracking-number {
            position: absolute;
            top: -1.5rem;
            right: 0;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.8rem;
            color: #90ae5e;
            letter-spacing: 0.05em;
        }

        .icon {
            width: 48px;
            height: 48px;
            margin-right: 1.25rem;
            position: relative;
        }

        .circle {
            position: absolute;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: rgba(197, 216, 158, 0.4);
            animation: pulse 2s infinite ease-in-out;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        h1 {
            color:rgb(83, 102, 48);
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        p {
            color:rgb(154, 183, 103);
            font-size: 1rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            max-width: 100%;
        }

        .error-code {
            display: inline-block;
            background-color:rgba(115, 137, 78, 0.83);
            color:rgb(142, 255, 86);
            padding: 0.2rem 0.5rem;
            border-radius: 2px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.875rem;
            margin-left: 0.25rem;
            letter-spacing: 0.02em;
            animation: blink 3s infinite;
            border: 1px solid rgba(115, 137, 78, 0.04);
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }

        .status-info {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #ebf1f5;
        }

        .status-item {
            text-align: center;
            flex: 1;
            position: relative;
        }

        .status-item:not(:last-child):after {
            content: "";
            position: absolute;
            top: 10px;
            right: 0;
            width: 1px;
            height: 20px;
            background-color:rgba(246, 251, 238, 0.94);
        }

        .status-label {
            font-size: 0.75rem;
            color:rgb(140, 163, 127);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .status-value {
            font-size: 0.875rem;
            color:rgb(50, 77, 29);
            font-weight: 500;
        }

        .warehouse {
            color:rgb(94, 131, 174);
        }

        .delivery {
            color: #d13913;
        }

        .moving-truck {
            position: absolute;
            bottom: 30px;
            left: -80px;
            width: 60px;
            height: 30px;
            animation: moveTruck 20s linear infinite;
        }

        @keyframes moveTruck {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(calc(100vw + 80px));
            }
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }

        .grid {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .grid-line {
            position: absolute;
            background-color: rgba(15, 98, 254, 0.05);
        }

        .horizontal {
            width: 100%;
            height: 1px;
        }

        .vertical {
            height: 100%;
            width: 1px;
        }

        @media (max-width: 500px) {
            .container {
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="grid" id="grid"></div>

    <div class="container">
        <div class="tracking-number">TRACKING: ERR-401-ACCESS</div>
        <div class="header">
            <div class="icon">
                <div class="circle"></div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#90ae5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
            <h1>Shipment Access Denied</h1>
        </div>

        <p>You do not have permission to track this shipment. <span class="error-code">ERR: <?php echo htmlspecialchars($errorcode); ?></span></p>

        <div class="status-info">
            <div class="status-item">
                <div class="status-label">Origin</div>
                <div class="status-value warehouse">You</div>
            </div>
            <div class="status-item">
                <div class="status-label">Status</div>
                <div class="status-value">Restricted</div>
            </div>
            <div class="status-item">
                <div class="status-label">Destination</div>
                <div class="status-value delivery">AGQ</div>
            </div>
        </div>
    </div>

    <svg class="moving-truck" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" fill="#90ae5e">
        <path d="M48 0C21.5 0 0 21.5 0 48V368c0 26.5 21.5 48 48 48H64c0 53 43 96 96 96s96-43 96-96H384c0 53 43 96 96 96s96-43 96-96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V288 256 237.3c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7H416V48c0-26.5-21.5-48-48-48H48zM416 160h50.7L544 237.3V256H416V160zM208 416c0 26.5-21.5 48-48 48s-48-21.5-48-48s21.5-48 48-48s48 21.5 48 48zm272 48c-26.5 0-48-21.5-48-48s21.5-48 48-48s48 21.5 48 48s-21.5 48-48 48z" />
    </svg>

    <script>
        // Create grid pattern in the background
        const gridContainer = document.getElementById('grid');

        // Create horizontal lines
        for (let i = 0; i < 20; i++) {
            const line = document.createElement('div');
            line.className = 'grid-line horizontal';
            line.style.top = `${i * 5}%`;
            line.style.animation = `fadeIn ${Math.random() * 1 + 0.5}s ease-out forwards ${Math.random() * 0.5}s`;
            gridContainer.appendChild(line);
        }

        // Create vertical lines
        for (let i = 0; i < 20; i++) {
            const line = document.createElement('div');
            line.className = 'grid-line vertical';
            line.style.left = `${i * 5}%`;
            line.style.animation = `fadeIn ${Math.random() * 1 + 0.5}s ease-out forwards ${Math.random() * 0.5}s`;
            gridContainer.appendChild(line);
        }
    </script>
</body>

</html>