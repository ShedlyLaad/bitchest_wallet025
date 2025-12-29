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
    table { width: 100%; margin-top: 14px; border-collapse: collapse; }
    td { padding: 10px 8px; border-top: 1px solid #1f2a3d; }
    .text-right { text-align: right; }
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

    <h2 style="margin:18px 0 8px; color:#fff;">Sell confirmation</h2>
    <p>Hello {{ $user->name }},</p>
    <p>Your sell order has been executed successfully.</p>

    <table>
      <tr>
        <td>Asset</td>
        <td class="text-right"><strong>{{ $symbol }}</strong></td>
      </tr>
      <tr>
        <td>Quantity sold</td>
        <td class="text-right"><strong>{{ $quantity }}</strong></td>
      </tr>
      <tr>
        <td>Unit price</td>
        <td class="text-right"><strong>{{ number_format($price, 2) }} €</strong></td>
      </tr>
      <tr>
        <td>Total received</td>
        <td class="text-right"><strong>{{ number_format($total,2) }} €</strong></td>
      </tr>
      <tr>
        <td>New EUR balance</td>
        <td class="text-right"><strong>{{ number_format($newBalance,2) }} €</strong></td>
      </tr>
    </table>

    <p class="muted" style="margin-top:16px;">If you did not make this transaction, contact support immediately.</p>
    <p class="muted" style="font-size:11px;">Soluyman,108 Av. République</p>
    <p class="muted" style="font-size:11px;">© {{ date('Y') }} Bit-Chest — Security and transparency.</p>
  </div>
</body>
</html>
