<!doctype html>
<html lang="ru">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f5f5f3;font-family:Arial,sans-serif;color:#2b2b2b;">
<div style="max-width:640px;margin:0 auto;padding:32px 18px;">
    <div style="background:#fff;border:1px solid #e8e8e6;border-radius:18px;overflow:hidden;">
        <div style="padding:24px 26px;border-bottom:1px solid #eee;">
            <div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#E91E63;font-weight:700;">Лика Вуле</div>
            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.25;">{{ $campaign->subject }}</h1>
        </div>
        <div style="padding:26px;line-height:1.7;font-size:16px;white-space:pre-wrap;">{{ $campaign->body }}</div>
    </div>
    <p style="margin:16px 4px;color:#8b8b8b;font-size:12px;line-height:1.5;">Вы получили это письмо, потому что разрешили маркетинговые сообщения. Если они вам больше не нужны, <a href="{{ $unsubscribeUrl }}" style="color:#777;">отмените маркетинговую подписку</a>.</p>
</div>
</body>
</html>
