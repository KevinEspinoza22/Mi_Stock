USE BD_Inventario;

ALTER TABLE Productos
    ADD COLUMN IF NOT EXISTS PrecioCompra DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER StockMinimo,
    ADD COLUMN IF NOT EXISTS PrecioVenta DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER PrecioCompra;

CREATE TABLE IF NOT EXISTS Compras (
    IdCompra INT AUTO_INCREMENT PRIMARY KEY,
    IdProveedor INT NOT NULL,
    IdUsuario INT NOT NULL,
    FechaCompra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    TipoDocumento VARCHAR(20) NOT NULL DEFAULT 'FACTURA',
    NumeroDocumento VARCHAR(50),
    ArchivoDocumento VARCHAR(255),
    Total DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT FK_Compras_Proveedor FOREIGN KEY (IdProveedor) REFERENCES Proveedores(IdProveedor),
    CONSTRAINT FK_Compras_Usuario FOREIGN KEY (IdUsuario) REFERENCES Usuarios(IdUsuario),
    INDEX IX_Compras_Fecha (FechaCompra)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS DetalleCompras (
    IdDetalleCompra INT AUTO_INCREMENT PRIMARY KEY,
    IdCompra INT NOT NULL,
    IdProducto INT NOT NULL,
    Cantidad INT NOT NULL,
    PrecioUnitario DECIMAL(12,2) NOT NULL DEFAULT 0,
    Subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT FK_DetalleCompras_Compra FOREIGN KEY (IdCompra) REFERENCES Compras(IdCompra),
    CONSTRAINT FK_DetalleCompras_Producto FOREIGN KEY (IdProducto) REFERENCES Productos(IdProducto),
    CHECK (Cantidad > 0),
    INDEX IX_DetalleCompras_Producto (IdProducto)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Ventas (
    IdVenta INT AUTO_INCREMENT PRIMARY KEY,
    IdUsuario INT NOT NULL,
    FechaVenta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    TipoDocumento VARCHAR(20) NOT NULL DEFAULT 'BOLETA',
    NumeroDocumento VARCHAR(50),
    Total DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT FK_Ventas_Usuario FOREIGN KEY (IdUsuario) REFERENCES Usuarios(IdUsuario),
    INDEX IX_Ventas_Fecha (FechaVenta)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS DetalleVentas (
    IdDetalleVenta INT AUTO_INCREMENT PRIMARY KEY,
    IdVenta INT NOT NULL,
    IdProducto INT NOT NULL,
    Cantidad INT NOT NULL,
    PrecioUnitario DECIMAL(12,2) NOT NULL DEFAULT 0,
    Subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT FK_DetalleVentas_Venta FOREIGN KEY (IdVenta) REFERENCES Ventas(IdVenta),
    CONSTRAINT FK_DetalleVentas_Producto FOREIGN KEY (IdProducto) REFERENCES Productos(IdProducto),
    CHECK (Cantidad > 0),
    INDEX IX_DetalleVentas_Producto (IdProducto)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ConfiguracionCorreo (
    IdConfiguracion INT AUTO_INCREMENT PRIMARY KEY,
    CorreoDestino VARCHAR(150) NOT NULL,
    AlertaStock TINYINT(1) NOT NULL DEFAULT 1,
    ReporteDiario TINYINT(1) NOT NULL DEFAULT 1,
    ReporteMensual TINYINT(1) NOT NULL DEFAULT 1,
    Activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO ConfiguracionCorreo (CorreoDestino, AlertaStock, ReporteDiario, ReporteMensual, Activo)
SELECT 'admin@logistock.cl', 1, 1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM ConfiguracionCorreo);

UPDATE Productos SET PrecioCompra=10000, PrecioVenta=15000 WHERE Codigo='TEC-001' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=6000, PrecioVenta=10000 WHERE Codigo='TEC-002' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=90000, PrecioVenta=140000 WHERE Codigo='TEC-003' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=4000, PrecioVenta=6000 WHERE Codigo='OFI-001' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=200, PrecioVenta=500 WHERE Codigo='OFI-002' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=2500, PrecioVenta=4500 WHERE Codigo='LIM-001' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=1000, PrecioVenta=2000 WHERE Codigo='LIM-002' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=50000, PrecioVenta=80000 WHERE Codigo='EQP-001' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=2500, PrecioVenta=4000 WHERE Codigo='INS-001' AND PrecioVenta=0;
UPDATE Productos SET PrecioCompra=5000, PrecioVenta=9000 WHERE Codigo='INS-002' AND PrecioVenta=0;
