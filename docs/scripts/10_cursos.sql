CREATE TABLE `cursos` (
  `cursoid` int(11) NOT NULL AUTO_INCREMENT,
  `cursodsc` varchar(120) NOT NULL,
  `cursodescrip` text NOT NULL,
  `cursoprecio` decimal(10,2) NOT NULL,
  `cursomodalidad` varchar(40) NOT NULL,
  `cursoest` char(3) DEFAULT 'ACT',
  PRIMARY KEY (`cursoid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `cursos` (`cursodsc`, `cursodescrip`, `cursoprecio`, `cursomodalidad`, `cursoest`) VALUES
('PHP MVC Basico', 'Curso de PHP MVC Basico con plantillero propio', 2500.00, 'Virtual', 'ACT');

INSERT INTO `cursos` (`cursodsc`, `cursodescrip`, `cursoprecio`, `cursomodalidad`, `cursoest`) VALUES
('PHP MVC Avanzado', 'Curso de PHP MVC Avanzado con PayPal y PixelPay', 3500.00, 'Virtual', 'ACT');

INSERT INTO `cursos` (`cursodsc`, `cursodescrip`, `cursoprecio`, `cursomodalidad`, `cursoest`) VALUES
('MariaDB para Desarrolladores', 'Curso de MariaDB enfocado en desarrolladores de aplicaciones web', 2800.00, 'Presencial', 'ACT');

INSERT INTO `cursos` (`cursodsc`, `cursodescrip`, `cursoprecio`, `cursomodalidad`, `cursoest`) VALUES
('Seguridad en Aplicaciones PHP', 'Curso de Seguridad aplicada a aplicaciones PHP MVC', 3000.00, 'Hibrido', 'ACT');
