<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error - Missing App Key</title>
    <style>
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 1.5rem;
            margin: -2rem -2rem 2rem -2rem;
            border-radius: 0.5rem 0.5rem 0 0;
            text-align: center;
        }
        h1 {
            margin: 0;
            font-size: 1.75rem;
        }
        pre {
            background-color: #f7f7f9;
            padding: 1rem;
            border-radius: 0.25rem;
            overflow: auto;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application Configuration Error</h1>
        </div>
        <h2>Missing Application Key</h2>
        <p>The application encryption key (APP_KEY) is not set.</p>
        <p>Run the following command to generate a new key:</p>
        <pre>php bsidlify key:generate</pre>
        <p>If you don't have a .env file yet, create one first:</p>
        <pre>cp .env.example .env
php bsidlify key:generate</pre>
    </div>
</body>
</html> 