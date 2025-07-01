<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>

<body>
    <h2>Welcome to Our Platform!</h2>
    <p>To complete your registration, please use the following One-Time Password (OTP) to verify your email:</p>
    <h3 style="color: #4CAF50;">{{ $otp }}</h3>
    <p>This OTP will expire in 10 minutes. If you didn't request this, please ignore this email.</p>
    <p>Thank you for registering!</p>
</body>

</html>
