<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Low Stock Alert</title>
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
    <div style="display: none; max-height: 0px; overflow: hidden;">Alert: Products are running low at your business. Check your inventory to restock.</div>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td bgcolor="#f4f4f4" align="center" style="padding: 20px 0 40px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="wrapper" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #EA580C;">
                    <tr>
                        <td bgcolor="#ffffff" align="center" style="padding: 30px 20px 20px 20px; border-bottom: 1px solid #eee;">
                            <img src="https://via.placeholder.com/200x60/ffffff/EA580C?text=Smart+Duuka+Logo" width="200" height="60" alt="Smart Duuka" style="display: block; border: 0; font-family: sans-serif; font-weight: bold; font-size: 24px; color: #EA580C;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 30px 20px 10px 20px;">
                            <img src="https://img.icons8.com/ios-filled/100/EA580C/out-of-stock.png" width="64" height="64" alt="Low Stock" style="display: block; margin-bottom: 15px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">Low Stock Alert</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">Hi <strong>{{username}}</strong>, some items at <strong>{{business_name}}</strong> have dropped below their reorder levels.</p>
                            <div style="background-color: #fff7ed; border-left: 4px solid #EA580C; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                                <p style="margin: 0 0 10px 0; font-size: 14px; color: #333333; font-weight: bold;">Items Requiring Attention:</p>
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 14px; color: #555;">
                                    <tr>
                                        <th align="left" style="padding-bottom: 5px; border-bottom: 1px solid #fed7aa;">Product Name</th>
                                        <th align="right" style="padding-bottom: 5px; border-bottom: 1px solid #fed7aa;">Current Qty</th>
                                        <th align="right" style="padding-bottom: 5px; border-bottom: 1px solid #fed7aa;">Reorder Lvl</th>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px dashed #fed7aa;">{{product_name_1}}</td>
                                        <td align="right" style="padding: 8px 0; border-bottom: 1px dashed #fed7aa; font-weight: bold; color: #EA580C;">{{current_qty_1}}</td>
                                        <td align="right" style="padding: 8px 0; border-bottom: 1px dashed #fed7aa;">{{reorder_level_1}}</td>
                                    </tr>
                                </table>
                            </div>
                            <p style="margin: 0 0 25px 0; font-size: 16px; line-height: 24px; color: #555555;">Please restock soon to ensure you don't miss out on any potential sales.</p>
                            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" style="border-radius: 50px;" bgcolor="#EA580C">
                                                    <a href="{{inventory_link}}" target="_blank" class="button" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 50px; border: 1px solid #EA580C; display: inline-block; font-weight: bold; box-shadow: 0 4px 6px rgba(234, 88, 12, 0.3);">View Inventory</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td bgcolor="#333333" align="center" style="padding: 40px 20px;"><p style="margin: 0 0 20px 0; color: #ffffff; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">We are here to help</p><p style="margin: 0 0 10px 0; font-size: 13px; color: #999999;">Call Support: <a href="tel:+256788948653" style="color: #ffffff; text-decoration: none;">+256 788 948 653</a></p><p style="margin: 0 0 10px 0; font-size: 13px; color: #999999;">Email: <a href="mailto:support@smartduuka.com" style="color: #ffffff; text-decoration: none;">support@smartduuka.com</a></p><p style="margin: 0 0 30px 0; font-size: 13px; color: #999999;">Web: <a href="https://www.smartduuka.com" style="color: #ffffff; text-decoration: none;">www.smartduuka.com</a></p><table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;"><tr><td align="center"><a href="wa.me/256788948653" target="_blank" style="text-decoration: none; margin: 0 12px;"><img src="https://img.icons8.com/ios-filled/50/ffffff/whatsapp.png" width="24" height="24" alt="WhatsApp" style="display: inline-block;"></a><a href="https://www.facebook.com/smartduukaug" target="_blank" style="text-decoration: none; margin: 0 12px;"><img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" width="24" height="24" alt="Facebook" style="display: inline-block;"></a><a href="https://www.instagram.com/smart.duuka" target="_blank" style="text-decoration: none; margin: 0 12px;"><img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" width="24" height="24" alt="Instagram" style="display: inline-block;"></a><a href="https://www.tiktok.com/@smartduuka" target="_blank" style="text-decoration: none; margin: 0 12px;"><img src="https://img.icons8.com/ios-filled/50/ffffff/tiktok.png" width="24" height="24" alt="TikTok" style="display: inline-block;"></a><a href="https://x.com/smartduuka" target="_blank" style="text-decoration: none; margin: 0 12px;"><img src="https://img.icons8.com/ios-filled/50/ffffff/twitterx.png" width="24" height="24" alt="X" style="display: inline-block;"></a></td></tr></table><div style="border-top: 1px solid #444444; width: 100%; max-width: 200px; margin-bottom: 20px;"></div><p style="margin: 0; font-size: 12px; color: #666666; line-height: 18px;">&copy; <span>{{date('Y')}}</span> Smart Duuka. Kampala, Uganda.<br>All rights reserved.<br><br><a href="https://smartduuka.com/privacy-policies" style="color: #999999; text-decoration: underline;">Privacy Policy</a> | <a href="https://smartduuka.com/terms" style="color: #999999; text-decoration: underline;">Terms and Conditions</a></p></td></tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>