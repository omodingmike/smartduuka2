<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Smart Duuka</title>
    <style>
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
            }

            .content {
                padding: 20px !important;
            }

            .cred-box {
                width: 100% !important;
                display: block !important;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f5; font-family: 'Arial', sans-serif; -webkit-font-smoothing: antialiased;">
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f5; padding: 20px 0;">
    <tr>
        <td align="center">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="container"
                   style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <tr>
                    <td align="center" style="background-color: #ea580c; padding: 40px 0;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">Welcome Aboard!</h1>
                        <p style="color: #fed7aa; margin: 10px 0 0 0; font-size: 16px;">Your Smart Duuka account is ready.</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="background-color: #ffffff; padding: 20px 0; border-bottom: 1px solid #f3f4f6;">
                        <img src="{{asset('logo.png')}}" alt="Company Logo" width="150" style="display: block; border: 0;">
                    </td>
                </tr>

                <tr>
                    <td class="content" style="padding: 40px;">
                        <p style="color: #4b5563; font-size: 16px; line-height: 1.6;">Hello {{$data['name']}},</p>
                        <p style="color: #4b5563; font-size: 16px; line-height: 1.6;">An account has been created for you at
                            <strong>{{$data['company_name']}}</strong>. You can now access the POS system to manage sales, inventory, and reports.</p>

                        <p style="color: #1f2937; font-weight: bold; margin-top: 25px;">Your Login Credentials:</p>

                        <table border="0" cellpadding="0" cellspacing="0" width="100%"
                               style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 30px;">
                            <tr>
                                <td style="padding: 20px;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding-bottom: 10px;">
                                                <span style="color: #166534; font-size: 12px; text-transform: uppercase; font-weight: bold;">Login Email</span><br>
                                                <span style="color: #1f2937; font-size: 16px;">{{$data['email']}}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 10px;">
                                                <span style="color: #166534; font-size: 12px; text-transform: uppercase; font-weight: bold;">Temporary Password</span><br>
                                                <span style="color: #1f2937; font-size: 16px; font-family: monospace; background: #ffffff; padding: 2px 5px;
                                                border-radius: 4px; border: 1px solid #d1d5db;">{{$data['password']}}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span style="color: #166534; font-size: 12px; text-transform: uppercase; font-weight: bold;">POS PIN</span><br>
                                                <span style="color: #1f2937; font-size: 16px; font-family: monospace; background: #ffffff; padding: 2px 5px;
                                                border-radius: 4px; border: 1px solid #d1d5db;">{{$data['pin']}}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="center">
                                    <a href="{{$data['login_url']}}"
                                       style="display: inline-block; background-color: #ea580c; color: #ffffff; font-size: 16px; font-weight: bold; text-decoration: none; padding: 14px 30px; border-radius: 6px;">Login
                                        to System</a>
                                </td>
                                <td>{{$data['login_url']}}</td>
                            </tr>
                        </table>

                        <p style="color: #ef4444; font-size: 13px; margin-top: 30px; background-color: #fef2f2; padding: 10px; border-radius: 6px; text-align: center;">
                            <strong>Security Alert:</strong> Please change your Password and PIN immediately after your first login via the Settings tab.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="background-color: #ffffff; padding: 10px 0 30px 0;">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 0 10px;">
                                    <a href="https://smartduuka.com" target="_blank"><img src="https://img.icons8.com/ios-filled/50/ea580c/internet.png"
                                                                                          alt="Website" width="24" height="24" style="display: block;"></a>
                                </td>
                                <td style="padding: 0 10px;">
                                    <a href="https://www.facebook.com/smartduukaug/" target="_blank"><img
                                                src="https://img.icons8.com/ios-filled/50/ea580c/facebook-new.png" alt="Facebook" width="24" height="24"
                                                style="display: block;"></a>
                                </td>
                                <td style="padding: 0 10px;">
                                    <a href="https://www.instagram.com/smart.duuka/" target="_blank"><img
                                                src="https://img.icons8.com/ios-filled/50/ea580c/instagram-new.png" alt="Instagram" width="24" height="24"
                                                style="display: block;"></a>
                                </td>
                                <td style="padding: 0 10px;">
                                    <a href="https://x.com/smartduuka" target="_blank"><img src="https://img.icons8.com/ios-filled/50/ea580c/twitterx--v1.png"
                                                                                            alt="X" width="24" height="24" style="display: block;"></a>
                                </td>
                                <td style="padding: 0 10px;">
                                    <a href="https://www.tiktok.com/@smartduuka" target="_blank"><img
                                                src="https://img.icons8.com/ios-filled/50/ea580c/tiktok.png" alt="TikTok" width="24" height="24"
                                                style="display: block;"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                        <p style="color: #4b5563; font-size: 13px; font-weight: bold; margin: 0 0 10px 0;">Smart Duuka is a product of Digi-volve Technologies
                            Limited.</p>
                        <p style="color: #9ca3af; font-size: 12px; margin: 0;">Sent by Smart Duuka POS System</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>