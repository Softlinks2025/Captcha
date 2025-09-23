<!DOCTYPE html>
<html>
<head>
    <title>500 Server Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .error-container {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .error-title {
            color: #721c24;
            margin-top: 0;
        }
        .error-message {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .trace {
            margin-top: 20px;
            font-size: 0.9em;
        }
        .trace pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">500 Server Error</h1>
        <p>An error occurred while processing your request.</p>
        
        @if(isset($message) && config('app.debug'))
            <h3>Error Details:</h3>
            <div class="error-message">{{ $message }}</div>
            
            @if(isset($file) && isset($line))
                <p><strong>File:</strong> {{ $file }} (Line: {{ $line }})</p>
            @endif
            
            @if(isset($trace))
                <div class="trace">
                    <h4>Stack Trace:</h4>
                    <pre>{{ $trace }}</pre>
                </div>
            @endif
        @endif
        
        <p><a href="{{ url()->previous() }}">Go back</a> or <a href="{{ url('/') }}">return to the homepage</a>.</p>
    </div>
</body>
</html>
