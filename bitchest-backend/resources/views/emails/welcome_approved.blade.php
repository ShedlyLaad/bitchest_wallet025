<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="color-scheme" content="dark light" />
  <meta name="supported-color-schemes" content="dark light" />
  <title>Welcome to BitChest</title>
  <!--[if mso]>
  <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
  <![endif]-->
  <style>
    /* Client resets */
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    a { color: #60a5fa; }

    @media only screen and (max-width: 620px) {
      .email-container { width: 100% !important; }
      .px { padding-left: 22px !important; padding-right: 22px !important; }
      .btn-a { display: block !important; width: 100% !important; box-sizing: border-box; text-align: center; }
    }
  </style>
</head>
<body style="margin:0; padding:0; background-color:#0f172a;">
  @php
    $logoCid = isset($message) ? $message->embed(public_path('images/logomail.png')) : (config('app.url') . '/images/logomail.png');
    $login   = $loginUrl ?? (rtrim(config('app.url'), '/') . '/login');
  @endphp

  <!-- Preheader (hidden preview text) -->
  <div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all;">
    Your BitChest account has been approved and is now active, with a &euro;500 starting balance.
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0f172a;">
    <tr>
      <td align="center" style="padding:32px 16px;">

        <table role="presentation" class="email-container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; background-color:#0b1220; border:1px solid #1f2a3d; border-radius:16px; overflow:hidden;">

          <!-- Header / brand -->
          <tr>
            <td class="px" align="left" style="padding:32px 40px 8px 40px;">
              <img src="{{ $logoCid }}" alt="BitChest" width="140" style="display:block; height:auto; max-height:64px; width:auto;" />
            </td>
          </tr>

          <!-- Title -->
          <tr>
            <td class="px" align="left" style="padding:16px 40px 0 40px;">
              <h1 style="margin:0; font-family:'Inter',Arial,Helvetica,sans-serif; font-size:24px; line-height:1.3; font-weight:700; color:#ffffff;">
                Welcome to BitChest
              </h1>
            </td>
          </tr>

          <!-- Body copy -->
          <tr>
            <td class="px" align="left" style="padding:16px 40px 0 40px; font-family:'Inter',Arial,Helvetica,sans-serif; font-size:15px; line-height:1.7; color:#e5e7eb;">
              <p style="margin:0 0 14px 0;">Hello {{ $name ?: 'there' }},</p>
              <p style="margin:0 0 14px 0;">Great news &mdash; the administrator has just approved your account.</p>
              <p style="margin:0;">You can now log in using your personal password.</p>
            </td>
          </tr>

          <!-- Status badge -->
          <tr>
            <td class="px" align="left" style="padding:20px 40px 0 40px;">
              <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="padding:8px 14px; background-color:rgba(52,211,153,0.12); border:1px solid rgba(52,211,153,0.35); border-radius:999px; font-family:'Inter',Arial,Helvetica,sans-serif; font-size:13px; font-weight:700; color:#34d399;">
                    &#9679;&nbsp; Your account is now active
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Starting balance card -->
          <tr>
            <td class="px" align="left" style="padding:24px 40px 0 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#111827; border:1px solid #1f2a3d; border-radius:12px;">
                <tr>
                  <td style="padding:18px 20px; font-family:'Inter',Arial,Helvetica,sans-serif; font-size:14px; line-height:1.6; color:#94a3b8;">
                    Starting balance
                    <div style="margin-top:4px; font-size:22px; font-weight:700; color:#34d399;">&euro;500.00</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

         
          <!-- Closing -->
          <tr>
            <td class="px" align="left" style="padding:24px 40px 0 40px; font-family:'Inter',Arial,Helvetica,sans-serif; font-size:14px; line-height:1.7; color:#94a3b8;">
              <p style="margin:0;">Thank you for joining BitChest &mdash; we&rsquo;re glad to have you on board.</p>
            </td>
          </tr>

          <!-- Divider -->
          <tr>
            <td class="px" style="padding:28px 40px 0 40px;">
              <div style="height:1px; line-height:1px; font-size:0; background-color:#1f2a3d;">&nbsp;</div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td class="px" align="left" style="padding:18px 40px 34px 40px; font-family:'Inter',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.7; color:#64748b;">
              <p style="margin:0;">Soluyman, 108 Av. R&eacute;publique</p>
              <p style="margin:4px 0 0 0;">&copy; {{ date('Y') }} BitChest &mdash; Security and transparency.</p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>
</body>
</html>
