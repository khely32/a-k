<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { color: #dc2626; font-size: 22px; margin: 0; }
        .code-box { text-align: center; background: #fef2f2; border: 2px dashed #dc2626; border-radius: 10px; padding: 20px; margin: 24px 0; }
        .code-box .code { font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #dc2626; font-family: monospace; }
        .footer { text-align: center; color: #888; font-size: 12px; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>A&K Motorcycle Parts</h1>
            <p style="color:#666;font-size:14px;">Password Reset Request</p>
        </div>
        <p>Hello <strong>{{ $userName }}</strong>,</p>
        <p>We received a request to reset your password. Use the code below to proceed:</p>
        <div class="code-box">
            <div class="code">{{ $code }}</div>
        </div>
        <p>This code will expire in <strong>10 minutes</strong>. If you did not request this, you can safely ignore this email.</p>
        <hr style="border:none;border-top:1px solid #eee;">
        <div class="footer">
            <p>A&K Motorcycle Parts and Accessories</p>
        </div>
    </div>
</body>
</html>
