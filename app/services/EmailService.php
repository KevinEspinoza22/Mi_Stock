<?php
require_once __DIR__ . '/../models/ConfiguracionModel.php';

class EmailService {
    private ConfiguracionModel $config;
    public function __construct() { $this->config = new ConfiguracionModel(); }

    private function send(string $to, string $subject, string $html): bool {
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
        $from = getenv('MAIL_FROM') ?: 'sistema@logistock.local';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$from}\r\n";
        return @mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $html, $headers);
    }

    public function enviarAlertaStock(array $productos): bool {
        $cfg = $this->config->obtener();
        if (!(int)$cfg['Activo'] || !(int)$cfg['AlertaStock'] || empty($productos)) return false;
        $rows = '';
        foreach ($productos as $p) {
            $rows .= '<tr><td style="padding:8px;border:1px solid #ddd">'.htmlspecialchars($p['Codigo']).'</td>'
                   .'<td style="padding:8px;border:1px solid #ddd">'.htmlspecialchars($p['Nombre']).'</td>'
                   .'<td style="padding:8px;border:1px solid #ddd">'.(int)$p['StockActual'].'</td>'
                   .'<td style="padding:8px;border:1px solid #ddd">'.(int)$p['StockMinimo'].'</td></tr>';
        }
        $html = '<h2>Alerta de stock bajo</h2><p>Los siguientes productos requieren revisión:</p>'
              .'<table cellpadding="0" cellspacing="0" style="border-collapse:collapse">'
              .'<tr><th style="padding:8px;border:1px solid #ddd">Código</th><th style="padding:8px;border:1px solid #ddd">Producto</th><th style="padding:8px;border:1px solid #ddd">Stock</th><th style="padding:8px;border:1px solid #ddd">Mínimo</th></tr>'
              .$rows.'</table>';
        return $this->send($cfg['CorreoDestino'], 'Alerta de stock bajo - Mi Stock', $html);
    }

    public function enviarReporteDiario(array $resumen): bool {
        $cfg = $this->config->obtener();
        if (!(int)$cfg['Activo'] || !(int)$cfg['ReporteDiario'] || empty($cfg['CorreoDestino'])) return false;
        $html = '<h2>Reporte diario de inventario</h2><p>Fecha: '.date('d/m/Y').'</p>'
              .'<ul><li>Ventas realizadas: '.(int)$resumen['ventas_hoy'].'</li>'
              .'<li>Total vendido hoy: $'.number_format($resumen['monto_hoy'],0,',','.').'</li>'
              .'<li>Total vendido este mes: $'.number_format($resumen['monto_mes'],0,',','.').'</li>'
              .'<li>Productos registrados: '.(int)$resumen['productos'].'</li>'
              .'<li>Productos con stock bajo: '.(int)$resumen['stock_bajo'].'</li></ul>';
        return $this->send($cfg['CorreoDestino'], 'Reporte diario de inventario - '.date('d/m/Y'), $html);
    }
}
