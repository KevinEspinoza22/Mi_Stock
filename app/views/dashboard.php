<?php
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();
require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../models/ProductoModel.php';
$dashboard = new DashboardModel();
$stats = $dashboard->resumen();
$ventas = $dashboard->ventasUltimosDias(7);
$criticos = (new ProductoModel())->obtenerStockCritico();
$labels = array_column($ventas, 'Dia');
$data = array_map('floatval', array_column($ventas, 'Total'));
?>
<!DOCTYPE html>
<html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard - Mi Stock</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark"><div class="container-fluid"><span class="navbar-brand fw-bold">Mi Stock</span><div class="text-white small"> <?=e($_SESSION['nombre'])?> · <?=e($_SESSION['rol'])?> <a class="btn btn-sm btn-outline-light ms-2" href="../controllers/LogoutController.php">Salir</a></div></div></nav>
<div class="container-fluid p-4">
<div class="d-flex flex-wrap gap-2 mb-4">
<a class="btn btn-primary" href="productos.php">📦 Inventario</a><a class="btn btn-success" href="pos.php">🛒 Ventas</a><a class="btn btn-warning" href="compras.php">📥 Compras</a><a class="btn btn-secondary" href="reportes.php">📊 Reportes</a><?php if ($_SESSION['rol']==='Administrador'): ?><a class="btn btn-dark" href="configuracion.php">⚙ Correo</a><?php endif; ?>
</div>
<div class="row g-3 mb-4">
<?php $cards=[['Productos',$stats['productos'],'primary'],['Stock bajo',$stats['stock_bajo'],'warning'],['Ventas hoy',$stats['ventas_hoy'],'success'],['Vendido hoy','$'.number_format($stats['monto_hoy'],0,',','.'),'info'],['Vendido mes','$'.number_format($stats['monto_mes'],0,',','.'),'dark'],['Valor inventario','$'.number_format($stats['valor_inventario'],0,',','.'),'secondary']]; foreach($cards as $c): ?>
<div class="col-6 col-md-4 col-xl-2"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-secondary small"><?=$c[0]?></div><div class="fs-4 fw-bold text-<?=$c[2]?> mt-1"><?=$c[1]?></div></div></div></div>
<?php endforeach; ?>
</div>
<div class="row g-4">
<div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold">Ventas últimos 7 días</div><div class="card-body"><canvas id="ventasChart" height="110"></canvas></div></div></div>
<div class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold">⚠ Productos con stock bajo</div><div class="card-body p-0"><div class="list-group list-group-flush"><?php if(!$criticos): ?><div class="p-3 text-success">No hay productos bajo el mínimo.</div><?php else: foreach(array_slice($criticos,0,8) as $p): ?><div class="list-group-item d-flex justify-content-between"><span><?=e($p['Nombre'])?></span><span class="badge bg-danger"><?=$p['StockActual']?> / <?=$p['StockMinimo']?></span></div><?php endforeach; endif; ?></div></div></div></div>
</div></div>
<script>new Chart(document.getElementById('ventasChart'),{type:'line',data:{labels:<?=json_encode($labels)?>,datasets:[{label:'Ventas',data:<?=json_encode($data)?>,tension:.3,fill:false}]},options:{responsive:true,scales:{y:{beginAtZero:true}}}});</script>
</body></html>
