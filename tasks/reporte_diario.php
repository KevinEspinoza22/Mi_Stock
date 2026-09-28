<?php
require_once __DIR__ . '/../app/models/DashboardModel.php';
require_once __DIR__ . '/../app/services/EmailService.php';
$resumen=(new DashboardModel())->resumen();
$ok=(new EmailService())->enviarReporteDiario($resumen);
echo $ok ? "Reporte diario enviado\n" : "Reporte diario no enviado (revisar configuración SMTP/correo)\n";
