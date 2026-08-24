<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <style>
    body { font-family: 'Inter', Arial, sans-serif; background: #0f172a; padding: 24px; }
    .card { max-width: 640px; margin: auto; background: #0b1220; border: 1px solid #1f2a3d; border-radius: 14px; padding: 28px; color: #e5e7eb; }
    .brand { display: inline-flex; align-items: center; gap: 10px; color: #60a5fa; font-weight: 700; letter-spacing: .3px; }
    .btn { display: inline-block; padding: 12px 18px; border-radius: 10px; background: linear-gradient(90deg, #2563eb, #1d4ed8); color: #fff; text-decoration: none; font-weight: 600; }
    .muted { color: #94a3b8; font-size: 13px; line-height: 1.6; }
    .status { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #34d399; margin: 4px 0; }
    .dot { width: 9px; height: 9px; border-radius: 50%; background: #34d399; box-shadow: 0 0 0 3px rgba(52,211,153,0.18); flex-shrink: 0; }
    .balance { color: #34d399; font-weight: 700; }
  </style>
</head>
<body>
  @php
    $logoCid = isset($message) ? $message->embed(public_path('images/logomail.png')) : (config('app.url') . '/images/logomail.png');
  @endphp
  <div class="card">
    <div class="brand">
      <img src="{{ $logoCid }}" alt="Bit-Chest Wallet" style="height:72px;" />
    </div>

    <h2 style="margin:18px 0 8px; color:#fff;">Welcome to BitChest!</h2>
    <p>Hello {{ $name ?: 'there' }},</p>

    <p>Great news — the administrator has just approved your account.</p>
    <p>You can now log in using your personal password.</p>

    <p class="status"><span class="dot"></span> Your account is now active</p>

    <p class="muted" style="margin-top:16px;">Your account has been initialized with a starting balance of <span class="balance">&euro;500</span>.</p>
    <p class="muted">Thank you for joining BitChest — we're glad to have you on board.</p>

    <p class="muted" style="font-size:11px; margin-top:24px;">Soluyman,108 Av. République</p>
    <p class="muted" style="font-size:11px;">© {{ date('Y') }} Bit-Chest — Security and transparency.</p>
  </div>
</body>
</html>
