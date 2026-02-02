<!DOCTYPE html>
<html>
<head>
    <title>Your Account Credentials</title>
</head>
<body>
<h1>Hello {{ $user->name }},</h1>
<p>Your account has been created/updated.</p>
<p>Here are your login credentials:</p>
<p><strong>Email:</strong> {{ $user->email }}</p>
<p><strong>Password:</strong> {{ $password }}</p>
@if($pin)
<p><strong>PIN:</strong> {{ $pin }}</p>
@endif
<p>Please change your password after logging in.</p>
<p>Thank you!</p>
</body>
</html>
