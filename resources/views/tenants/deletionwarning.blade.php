<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Final Notice: Account Data Scheduled for Deletion</title>
    <style>
        /* CLIENT-SPECIFIC STYLES */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
        }

        /* RESET STYLES */
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        table {
            border-collapse: collapse !important;
        }

        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333333;
        }

        /* IOS BLUE LINKS */
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        /* MOBILE STYLES */
        @media screen and (max-width: 600px) {
            .wrapper {
                width: 100% !important;
                max-width: 100% !important;
            }

            .mobile-padding {
                padding: 30px 20px !important;
            }

            .mobile-center {
                text-align: center !important;
            }

            .two-col {
                width: 100% !important;
                display: block !important;
            }
        }

        /* HOVER EFFECTS */
        .button:hover {
            background-color: #e65c00 !important;
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f4f4f4;">

<div style="display: none; max-height: 0px; overflow: hidden;">
    Action Required: Your inactive Smart Duuka business data is scheduled for deletion soon. Renew to save your records.
</div>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="#f4f4f4" align="center" style="padding: 20px 0 40px 0;">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="wrapper"
                   style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #ff6600;">

                <tr>
                    <td bgcolor="#ffffff" align="center"
                        style="padding: 30px 20px 20px 20px; border-bottom: 1px solid #eee;">
                        <img src="{{asset('logo.png')}}"
                             width="200" height="60" alt="Smart Duuka"
                             style="display: block; border: 0; font-family: sans-serif; font-weight: bold; font-size: 24px; color: #ff6600;">
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding: 30px 20px 10px 20px;">
                        <img src="https://img.icons8.com/ios-filled/100/d9534f/delete-database.png" width="64"
                             height="64" alt="Database Warning" style="display: block; margin-bottom: 15px;">
                        <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #d9534f;">Data Deletion Notice</h1>
                    </td>
                </tr>

                <tr>
                    <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
                        <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">
                            Hi <strong><?php echo $username; ?></strong>, this is an important system notification regarding your inactive Smart Duuka account.
                        </p>

                        <p style="margin: 0 0 25px 0; font-size: 16px; line-height: 24px; color: #555555;">
                            It has been over <strong><?php echo $inactive_days; ?> days</strong> since your subscription for <strong><?php echo $business_name; ?></strong>
                            expired. To maintain server efficiency and comply with our data retention policies, inactive business records are scheduled for
                            permanent deletion.
                        </p>

                        <div
                                style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 25px;">
                            <p style="margin: 0; font-size: 15px; color: #721c24; line-height: 22px;">
                                <strong>Warning:</strong> All your historical sales data, inventory logs, and customer records will be permanently
                                deleted in <strong style="font-size: 18px;"><?php echo $days_until_deletion; ?> days</strong>. Once deleted, this data cannot be recovered.
                            </p>
                        </div>

                        <p style="margin: 0 0 25px 0; font-size: 16px; line-height: 24px; color: #555555;">
                            If you wish to keep your business records intact, please reactivate your subscription before the deletion date.
                        </p>

                        <table border="0" cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="center" style="border-radius: 50px;" bgcolor="#ff6600">
                                                <a href="<?php echo $login_link; ?>" target="_blank" class="button"
                                                   style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 50px; border: 1px solid #ff6600; display: inline-block; font-weight: bold; box-shadow: 0 4px 6px rgba(255, 102, 0, 0.3);">
                                                    Renew & Save My Data
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <tr>
                    <td bgcolor="#333333" align="center" style="padding: 40px 20px;">
                        <p
                                style="margin: 0 0 20px 0; color: #ffffff; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                            We are here to help</p>

                        <p style="margin: 0 0 10px 0; font-size: 13px; color: #999999;">
                            Call Support: <a href="tel:+256788948653"
                                             style="color: #ffffff; text-decoration: none;">+256 788 948 653</a>
                        </p>
                        <p style="margin: 0 0 10px 0; font-size: 13px; color: #999999;">
                            Email: <a href="mailto:support@smartduuka.com"
                                      style="color: #ffffff; text-decoration: none;">support@smartduuka.com</a>
                        </p>
                        <p style="margin: 0 0 30px 0; font-size: 13px; color: #999999;">
                            Web: <a href="https://www.smartduuka.com"
                                    style="color: #ffffff; text-decoration: none;">www.smartduuka.com</a>
                        </p>

                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                            <tr>
                                <td align="center">
                                    <a href="wa.me/256788948653" target="_blank"
                                       style="text-decoration: none; margin: 0 12px;">
                                        <img src="https://img.icons8.com/ios-filled/50/ffffff/whatsapp.png"
                                             width="24" height="24" alt="WhatsApp" style="display: inline-block;">
                                    </a>
                                    <a href="https://www.facebook.com/smartduukaug" target="_blank"
                                       style="text-decoration: none; margin: 0 12px;">
                                        <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png"
                                             width="24" height="24" alt="Facebook" style="display: inline-block;">
                                    </a>
                                    <a href="https://www.instagram.com/smart.duuka" target="_blank"
                                       style="text-decoration: none; margin: 0 12px;">
                                        <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png"
                                             width="24" height="24" alt="Instagram" style="display: inline-block;">
                                    </a>
                                    <a href="https://www.tiktok.com/@smartduuka" target="_blank"
                                       style="text-decoration: none; margin: 0 12px;">
                                        <img src="https://img.icons8.com/ios-filled/50/ffffff/tiktok.png" width="24"
                                             height="24" alt="TikTok" style="display: inline-block;">
                                    </a>
                                    <a href="https://x.com/smartduuka" target="_blank"
                                       style="text-decoration: none; margin: 0 12px;">
                                        <img src="https://img.icons8.com/ios-filled/50/ffffff/twitterx.png"
                                             width="24" height="24" alt="X" style="display: inline-block;">
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <div
                                style="border-top: 1px solid #444444; width: 100%; max-width: 200px; margin-bottom: 20px;">
                        </div>

                        <p style="margin: 0; font-size: 12px; color: #666666; line-height: 18px;">
                            &copy; <span>{{date('Y')}}</span> Smart Duuka. Kampala, Uganda.<br>All rights reserved.<br><br>
                            <a href="https://smartduuka.com/privacy-policies"
                               style="color: #999999; text-decoration: underline;">Privacy Policy</a> |
                            <a href="https://smartduuka.com/terms"
                               style="color: #999999; text-decoration: underline;">Terms and Conditions</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


</body>

</html>