<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Invoice</title>
    <style>
    </style>
</head>
<body>
<div class="content">
    <h2>Hello {{ $order->user->name }},</h2>

    <p>Please find your invoice attached to this email for your records.</p>

    Thanks,
    {{ config('app.name') }}
</div>
</body>
</html>
