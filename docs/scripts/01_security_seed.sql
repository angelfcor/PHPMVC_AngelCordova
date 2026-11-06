INSERT INTO `roles` (`rolescod`, `rolesdsc`, `rolesest`) VALUES
('ROL_ADM', 'Administrador', 'ACT');

INSERT INTO `roles` (`rolescod`, `rolesdsc`, `rolesest`) VALUES
('ROL_USR', 'Usuario', 'ACT');

INSERT INTO `funciones` (`fncod`, `fndsc`, `fnest`, `fntyp`) VALUES
('FNC_PRODUCTS', 'WW Productos', 'ACT', 'FNC');

INSERT INTO `funciones` (`fncod`, `fndsc`, `fnest`, `fntyp`) VALUES
('FNC_USERS', 'WW Usuarios', 'ACT', 'FNC');

INSERT INTO `funciones` (`fncod`, `fndsc`, `fnest`, `fntyp`) VALUES
('FNC_ROLES', 'WW Roles', 'ACT', 'FNC');

INSERT INTO `funciones` (`fncod`, `fndsc`, `fnest`, `fntyp`) VALUES
('FNC_FUNCIONES', 'WW Funciones', 'ACT', 'FNC');

INSERT INTO `usuario` (`useremail`, `username`, `userpswd`, `userfching`, `userpswdest`, `userest`, `usertipo`) VALUES
('admin@correo.local', 'Administrador', 'admin123', NOW(), 'ACT', 'ACT', 'NOR');

INSERT INTO `roles_usuarios` (`usercod`, `rolescod`, `roleuserest`, `roleuserfch`) VALUES
(1, 'ROL_ADM', 'ACT', NOW());
