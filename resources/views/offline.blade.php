<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed layout-wide" dir="ltr" data-theme="theme-default"
  data-assets-path="/assets/" data-template="vertical-menu-template-no-customizer">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Offline - WHS4</title>
  <meta name="description" content="WHS4 is currently offline" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet">

  <!-- Core CSS -->
  <link rel="stylesheet" href="/assets/vendor/css/core.css" />
  <link rel="stylesheet" href="/assets/vendor/css/theme-default.css" />
  <link rel="stylesheet" href="/assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <style>
    .offline-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 2rem;
      text-align: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .offline-icon {
      font-size: 6rem;
      margin-bottom: 2rem;
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% {
        opacity: 1;
      }
      50% {
        opacity: 0.5;
      }
    }

    .offline-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .offline-message {
      font-size: 1.25rem;
      margin-bottom: 2rem;
      max-width: 600px;
      line-height: 1.6;
    }

    .offline-actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      justify-content: center;
    }

    .offline-btn {
      padding: 0.75rem 2rem;
      font-size: 1rem;
      font-weight: 600;
      border: 2px solid white;
      border-radius: 0.5rem;
      background: rgba(255, 255, 255, 0.1);
      color: white;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .offline-btn:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-2px);
    }

    .offline-btn-primary {
      background: white;
      color: #7367f0;
    }

    .offline-btn-primary:hover {
      background: rgba(255, 255, 255, 0.9);
    }

    .offline-features {
      margin-top: 3rem;
      max-width: 800px;
    }

    .offline-features-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .offline-feature-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .offline-feature-item {
      background: rgba(255, 255, 255, 0.1);
      padding: 1.5rem;
      border-radius: 0.5rem;
      backdrop-filter: blur(10px);
    }

    .offline-feature-icon {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .offline-feature-text {
      font-size: 1rem;
      font-weight: 500;
    }

    @media (max-width: 768px) {
      .offline-title {
        font-size: 2rem;
      }

      .offline-message {
        font-size: 1rem;
      }

      .offline-icon {
        font-size: 4rem;
      }
    }
  </style>
</head>

<body>
  <div class="offline-container">
    <div class="offline-icon">
      📡
    </div>

    <h1 class="offline-title">You're Offline</h1>

    <p class="offline-message">
      Don't worry! WHS4 works offline. You can continue reporting incidents, viewing cached data, and using many
      features. Your changes will sync automatically when you're back online.
    </p>

    <div class="offline-actions">
      <button class="offline-btn offline-btn-primary" onclick="window.location.reload()">
        Try Again
      </button>
      <a href="/" class="offline-btn">
        Go to Dashboard
      </a>
    </div>

    <div class="offline-features">
      <h2 class="offline-features-title">What You Can Do Offline</h2>
      <ul class="offline-feature-list">
        <li class="offline-feature-item">
          <div class="offline-feature-icon">📝</div>
          <div class="offline-feature-text">Report Incidents</div>
        </li>
        <li class="offline-feature-item">
          <div class="offline-feature-icon">📊</div>
          <div class="offline-feature-text">View Cached Data</div>
        </li>
        <li class="offline-feature-item">
          <div class="offline-feature-icon">📸</div>
          <div class="offline-feature-text">Take Photos</div>
        </li>
        <li class="offline-feature-item">
          <div class="offline-feature-icon">🔄</div>
          <div class="offline-feature-text">Auto-Sync Later</div>
        </li>
      </ul>
    </div>
  </div>

  <script>
    // Auto-retry connection every 10 seconds
    setInterval(() => {
      if (navigator.onLine) {
        window.location.reload();
      }
    }, 10000);

    // Listen for online event
    window.addEventListener('online', () => {
      window.location.reload();
    });
  </script>
</body>

</html>
