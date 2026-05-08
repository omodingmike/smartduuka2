<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Restore Your Smart Duuka Access</title>
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

    <!-- Preheader Text (Hidden) -->
    <div style="display: none; max-height: 0px; overflow: hidden;">
        Your Smart Duuka subscription has expired. Restore access today to continue managing your business.
    </div>

    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td bgcolor="#f4f4f4" align="center" style="padding: 20px 0 40px 0;">

                <!-- Main Content Wrapper -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="wrapper"
                    style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);">

                    <!-- LOGO HEADER -->
                    <tr>
                        <td bgcolor="#ffffff" align="center"
                            style="padding: 30px 20px 20px 20px; border-bottom: 3px solid #ff6600;">
                            <!-- REPLACE WITH YOUR ACTUAL LOGO URL -->
                            <!-- Suggested size: 200px wide -->
                            <img src="{{asset('logo.png')}}"
                                width="200" height="60" alt="Smart Duuka"
                                style="display: block; border: 0; color: #ff6600; font-family: sans-serif; font-weight: bold; font-size: 24px;">
                        </td>
                    </tr>

                    <!-- HERO / ALERT SECTION -->
                    <tr>
                        <td bgcolor="#fff4e6" align="center" style="padding: 40px 20px;">
                            <img src="https://img.icons8.com/ios-filled/100/ff6600/time-span.png" width="64" height="64"
                                alt="Clock" style="display: block; margin-bottom: 15px;">
                            <h1 style="margin: 0; font-size: 26px; color: #333333; letter-spacing: -0.5px;">Subscription
                                Paused</h1>
                            <p style="margin: 10px 0 0 0; color: #666666; font-size: 16px;">We miss you, let's get you
                                back online.</p>
                        </td>
                    </tr>

                    <!-- BODY CONTENT -->
                    <tr>
                        <td class="mobile-padding" style="padding: 40px 50px 30px 50px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 26px; color: #555555;">
                                Hi <strong>{{$username}}</strong>,
                            </p>
                            <p style="margin: 0 0 25px 0; font-size: 16px; line-height: 26px; color: #555555;">
                                It has been <strong style="color: #ff6600; font-size: 18px;">{{$days_past}} days</strong>
                                since your subscription expired. To ensure you don't lose track of your inventory and
                                sales data, please renew your account.
                            </p>

                            <!-- BUSINESS INFO CARD -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                style="background-color: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td width="30" valign="top" style="padding-right: 10px;">
                                                    <img src="https://img.icons8.com/ios-filled/50/ff6600/shop.png"
                                                        width="24" height="24" alt="Shop">
                                                </td>
                                                <td valign="top">
                                                    <span
                                                        style="font-size: 12px; color: #999999; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;">Business
                                                        Name</span>
                                                    <div
                                                        style="font-size: 16px; color: #333333; font-weight: bold; margin-top: 4px;">
                                                        {{$business_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="height: 15px;"></td>
                                            </tr>
                                            <tr>
                                                <td width="30" valign="top" style="padding-right: 10px;">
                                                    <img src="https://img.icons8.com/ios-filled/50/ff6600/name-tag.png"
                                                        width="24" height="24" alt="ID">
                                                </td>
                                                <td valign="top">
                                                    <span
                                                        style="font-size: 12px; color: #999999; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;">Business
                                                        ID</span>
                                                    <div
                                                        style="font-size: 16px; color: #333333; font-weight: bold; margin-top: 4px; font-family: monospace;">
                                                        {{$business_id}}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- CALL TO ACTION BUTTON -->
                            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-bottom: 40px;">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" style="border-radius: 50px;" bgcolor="#ff6600">
                                                    <a href="{{$login_link}}" target="_blank" class="button"
                                                        style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 50px; border: 1px solid #ff6600; display: inline-block; font-weight: bold; box-shadow: 0 4px 6px rgba(255, 102, 0, 0.3);">
                                                        Restore My Account
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- SEPARATOR -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="border-top: 1px solid #eeeeee; height: 30px;"></td>
                                </tr>
                            </table>

                            <!-- PAYMENT INSTRUCTIONS -->
                            <h3 style="margin: 0 0 20px 0; font-size: 18px; color: #333333; text-align: center;">Quick
                                Ways to Pay</h3>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <!-- Option 1 -->
                                    <td class="two-col" valign="top" width="48%"
                                        style="background-color: #ffffff; border: 1px solid #eeeeee; border-radius: 8px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding: 20px; text-align: center;">
                                                    <img src="https://img.icons8.com/ios-filled/50/ff6600/globe.png"
                                                        width="32" height="32" style="margin-bottom: 10px;">
                                                    <div style="font-weight: bold; color: #333333; margin-bottom: 5px;">
                                                        Online</div>
                                                    <div style="font-size: 13px; color: #777777; line-height: 20px;">
                                                        Login to Smart Duuka <br> Select Package <br> Pay via Mobile
                                                        Money
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <!-- Spacer -->
                                    <td width="4%" style="font-size: 0; line-height: 0;">&nbsp;</td>

                                    <!-- Option 2 -->
                                    <td class="two-col" valign="top" width="48%"
                                        style="background-color: #ffffff; border: 1px solid #eeeeee; border-radius: 8px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding: 20px; text-align: center;">
                                                    <img src="https://img.icons8.com/ios-filled/50/25D366/whatsapp.png"
                                                        width="32" height="32" style="margin-bottom: 10px;">
                                                    <div style="font-weight: bold; color: #333333; margin-bottom: 5px;">
                                                        WhatsApp</div>
                                                    <div style="font-size: 13px; color: #777777; line-height: 20px;">
                                                        Chat <strong>+256 771 915 925</strong> <br> Send ID:
                                                        <strong>{{$business_id}}</strong> <br> Follow prompts
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- FOOTER -->
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

                            <!-- Social Media Icons -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <!-- WhatsApp -->
                                        <a href="wa.me/256788948653" target="_blank"
                                            style="text-decoration: none; margin: 0 12px;">
                                            <img src="https://img.icons8.com/ios-filled/50/ffffff/whatsapp.png"
                                                width="24" height="24" alt="WhatsApp" style="display: inline-block;">
                                        </a>
                                        <!-- Facebook -->
                                        <a href="https://www.facebook.com/smartduukaug" target="_blank"
                                            style="text-decoration: none; margin: 0 12px;">
                                            <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png"
                                                width="24" height="24" alt="Facebook" style="display: inline-block;">
                                        </a>
                                        <!-- Instagram -->
                                        <a href="https://www.instagram.com/smart.duuka" target="_blank"
                                            style="text-decoration: none; margin: 0 12px;">
                                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png"
                                                width="24" height="24" alt="Instagram" style="display: inline-block;">
                                        </a>
                                        <!-- TikTok -->
                                        <a href="https://www.tiktok.com/@smartduuka" target="_blank"
                                            style="text-decoration: none; margin: 0 12px;">
                                            <img src="https://img.icons8.com/ios-filled/50/ffffff/tiktok.png" width="24"
                                                height="24" alt="TikTok" style="display: inline-block;">
                                        </a>
                                        <!-- Twitter X -->
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
                                <a href="https://smartduuka.com/privacy-policies" style="color: #999999; text-decoration: underline;">Privacy Policy</a> |
                                <a href="https://smartduuka.com/terms" style="color: #999999; text-decoration: underline;">Terms and Conditions</a>
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Unsubscribe / Browser Link (Optional) -->
                <p style="margin-top: 20px; font-size: 12px; color: #999999;">
                    <a href="#" style="color: #999999; text-decoration: underline;">View in browser</a> | <a href="#"
                        style="color: #999999; text-decoration: underline;">Unsubscribe</a>
                </p>

            </td>
        </tr>
    </table>

    <!-- JavaScript to update the year -->

</body>

</html>