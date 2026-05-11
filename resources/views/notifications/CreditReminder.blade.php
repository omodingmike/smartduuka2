<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Friendly Balance Reminder</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; font-family: 'Helvetica', 'Arial', sans-serif; background-color: #f4f4f4; color: #333333; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        @media screen and (max-width: 600px) { .wrapper { width: 100% !important; max-width: 100% !important; } .mobile-padding { padding: 30px 20px !important; } }
        .button:hover { background-color: #c2410c !important; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="display: none; max-height: 0px; overflow: hidden;">A gentle reminder regarding your outstanding balance.</div>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td bgcolor="#f4f4f4" align="center" style="padding: 20px 0 40px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="wrapper" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #EA580C;">
                    <tr>
                        <td bgcolor="#ffffff" align="center" style="padding: 30px 20px 20px 20px; border-bottom: 1px solid #eee;">
                            <img src="https://via.placeholder.com/200x60/ffffff/EA580C?text={{business_name}}" width="200" height="60" alt="{{business_name}}" style="display: block; border: 0; font-family: sans-serif; font-weight: bold; font-size: 24px; color: #333;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 30px 20px 10px 20px;">
                            <img src="https://img.icons8.com/ios-filled/100/EA580C/notification.png" width="64" height="64" alt="Reminder" style="display: block; margin-bottom: 15px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">Friendly Reminder</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">Hi <strong>{{customer_name}}</strong>, this is a gentle reminder regarding your outstanding credit balance of UGX {{debt_balance}} at {{business_name}}.</p>
                            <div style="background-color: #fff7ed; border-left: 4px solid #EA580C; padding: 15px; margin-bottom: 25px; border-radius: 4px; text-align: center;">
                                <p style="margin: 0; font-size: 18px; color: #EA580C; font-weight: bold;">Amount Due: UGX {{debt_balance}}</p>
                                <p style="margin: 5px 0 0 0; font-size: 14px; color: #555555;">Due Date: <strong>{{due_date}}</strong></p>
                            </div>
                            <p style="margin: 0 0 25px 0; font-size: 16px; line-height: 24px; color: #555555; text-align: center;">Please arrange for payment by {{due_date}}. If you have already paid, please ignore this message. Thank you for your business! [cite: 40]</p>
                            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" style="border-radius: 50px;" bgcolor="#EA580C">
                                                    <a href="{{payment_link}}" target="_blank" class="button" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 50px; border: 1px solid #EA580C; display: inline-block; font-weight: bold; box-shadow: 0 4px 6px rgba(234, 88, 12, 0.3);">View Details to Pay</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td bgcolor="#333333" align="center" style="padding: 40px 20px;"><p style="margin: 0 0 20px 0; color: #ffffff; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Powered By Smart Duuka</p><p style="margin: 0; font-size: 12px; color: #666666; line-height: 18px;">&copy; <span>{{date('Y')}}</span> Smart Duuka. Kampala, Uganda.<br><a href="https://www.smartduuka.com" style="color: #999999; text-decoration: underline;">www.smartduuka.com</a></p></td></tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>