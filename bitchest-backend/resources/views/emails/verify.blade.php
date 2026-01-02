<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <style>
    body { font-family: 'Inter', Arial, sans-serif; background: #0f172a; padding: 24px; }
    .card { max-width: 640px; margin: auto; background: #0b1220; border: 1px solid #1f2a3d; border-radius: 14px; padding: 28px; color: #e5e7eb; }
    .brand { display: inline-flex; align-items: center; gap: 10px; color: #60a5fa; font-weight: 700; letter-spacing: .3px; }
    .badge { display:inline-block; padding:6px 10px; border-radius: 999px; background: #1e3a8a; color:#bfdbfe; font-size:12px; }
    .btn { display: inline-block; padding: 12px 18px; border-radius: 10px; background: linear-gradient(90deg, #2563eb, #1d4ed8); color: #fff; text-decoration: none; font-weight: 600; }
    .muted { color: #94a3b8; font-size: 13px; line-height: 1.6; }
  </style>
</head>
<body>
  @php
    $logoCid = isset($message) ? $message->embed(public_path('images/bitchest_Footer.png')) : (config('app.url') . '/images/bitchest_Footer.png');
    $displayName = $name ?? ($user->name ?? ($user->first_name && $user->last_name ? $user->first_name . ' ' . $user->last_name : $user->email ?? 'User'));
  @endphp
  <div class="card">
    <div class="brand">
      <img src="{{ $logoCid }}" alt="Bit-Chest Wallet" style="height:90px;" />
    </div>

    <h2 style="margin:18px 0 8px; color:#fff;">Welcome to Bit-Chest</h2>
    <p>Hello {{ $displayName }},</p>
    <p>You’re officially in — your account is approved and ready to use.</p>
    <p style="margin:12px 0;">A welcome credit of <strong>€500</strong> is already in your wallet. Think of it as your first step into the world of smart trading.</p>
    <p class="muted" style="margin-top:8px; color:#35a7ff;">Explore. Learn. Trade. Every move you make is a step closer to mastering the market.</p>
    <p class="muted" style="margin-top:8px; color:#35a7ff;">Your portfolio is ready. Your opportunity starts now.</p>
    <p style="margin:14px 0 6px; color:#01ff19; font-weight:700;">Let’s build your future — one trade at a time.</p>

    <p class="muted" style="margin-top:16px;">If you did not request this, you can safely ignore this email.</p>
    <p class="muted" style="font-size:11px;">Soluyman,108 Av. République</p>
    <p class="muted" style="font-size:11px;">© {{ date('Y') }} Bit-Chest — Security and transparency.</p>
  </div>
</body>
</html>

