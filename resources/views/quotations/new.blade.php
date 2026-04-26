<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Your Quotation from {{company()['company_name']}}</title>
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

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="#f4f4f4" align="center" style="padding: 20px 0 40px 0;">

            <!-- Main Container -->
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="wrapper"
                   style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #ff6600;">

                <!-- HEADER / SENDER LOGO -->
                <tr>
                    <td bgcolor="#ffffff" align="center"
                        style="padding: 30px 20px 20px 20px; border-bottom: 1px solid #eee;">
                        <!-- This logo should dynamically load the sender's business logo, falling back to text if unavailable -->
                        <img src="{{logo()}}" width="150" alt="{{company()['company_name']}}"
                             style="display: block; border: 0; font-family: sans-serif; font-weight: bold; font-size: 24px; color: #333333; max-height: 80px; object-fit: contain;">
                    </td>
                </tr>

                <!-- HERO TITLE -->
                <tr>
                    <td align="center" style="padding: 30px 20px 10px 20px;">
                        <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #ff6600;">Your Quotation is
                            Ready</h1>
                    </td>
                </tr>

                <!-- BODY CONTENT -->
                <tr>
                    <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
                        <h2 style="margin: 0 0 20px 0; font-size: 20px; color: #333333;">Hi {{$order->user->name}},</h2>

                        <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #666666;">
                            Thank you for your interest. We are pleased to provide you with the quotation you
                            requested from <strong>{{company()['company_name']}}</strong>.
                        </p>

                        <!-- Quotation Summary Box -->
                        <div
                                style="background-color: #fff4e6; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                            <h3
                                    style="margin: 0 0 15px 0; font-size: 16px; color: #ff6600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #ffd4b3; padding-bottom: 10px;">
                                Quotation Summary
                            </h3>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                   style="font-size: 14px; color: #555;">
                                <tr>
                                    <td style="padding-bottom: 8px;"><strong>Quotation No:</strong></td>
                                    <td align="right" style="padding-bottom: 8px;">{{$order->order_serial_no}}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 8px;"><strong>Date Issued:</strong></td>
                                    <td align="right" style="padding-bottom: 8px;">{{$order->order_datetime}}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 8px;"><strong>Valid Until:</strong></td>
                                    <td align="right" style="padding-bottom: 8px;">{{datetime($order->due_date)}}</td>
                                </tr>
                                <tr>
                                    <td
                                            style="padding-top: 10px; border-top: 1px dashed #ffcca6; font-size: 16px; color: #333;">
                                        <strong>Total Amount:</strong></td>
                                    <td align="right"
                                        style="padding-top: 10px; border-top: 1px dashed #ffcca6; font-size: 16px; color: #333;">
                                        <strong> {{currency($order->total)}}</strong></td>
                                </tr>
                            </table>
                        </div>

                        <!-- PDF Attachment Notice -->
                        <div
                                style="background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; margin-bottom: 30px; text-align: center;">
                            <p style="margin: 0; font-size: 15px; color: #444444; line-height: 22px;">
                                📄 A detailed breakdown of the costs and services is <strong>attached to this email
                                    as a PDF</strong>. Please review it at your convenience.
                            </p>
                        </div>

                        <p
                                style="margin: 0 0 25px 0; font-size: 15px; line-height: 24px; color: #666666; text-align: center;">
                            If you are happy to proceed, please click the button below to accept the quotation or
                            let us know if you have any questions.
                        </p>

                        <!-- CTA Button -->
                        <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-bottom: 10px;">
                            <tr>
                                <td align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="center" style="border-radius: 50px;" bgcolor="#ff6600">
                                                <a href="{{tenant('frontend_url')}}/share/quotation/{{$order->id}}" target="_blank" class="button"
                                                   style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 50px; border: 1px solid #ff6600; display: inline-block; font-weight: bold; box-shadow: 0 4px 6px rgba(255, 102, 0, 0.3);">
                                                    Review & Accept Quotation
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- FOOTER (Smart Duuka Branding) -->
                <tr>
                    <td bgcolor="#333333" align="center" style="padding: 40px 20px;">
                        <p
                                style="margin: 0 0 20px 0; color: #ffffff; font-weight: bold; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                            Powered securely by Smart Duuka
                        </p>

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

                        <p style="margin: 0; font-size: 12px; color: #666666;">
                            &copy; {{date('Y')}} Smart Duuka. Kampala, Uganda.<br>All rights reserved.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>