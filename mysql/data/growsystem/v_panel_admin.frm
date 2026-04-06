TYPE=VIEW
query=select `d`.`id` AS `id`,`d`.`numero_serie` AS `numero_serie`,`d`.`estado` AS `estado`,`d`.`fecha_creacion` AS `fecha_creacion`,`d`.`fecha_asignacion` AS `fecha_asignacion`,`u`.`nombre` AS `usuario_nombre`,`u`.`email` AS `usuario_email`,`i`.`alias` AS `instancia_alias`,`i`.`activa` AS `instancia_activa`,`t`.`token` AS `api_token`,`t`.`ultimo_uso` AS `token_ultimo_uso` from (((`growsystem`.`dispositivos` `d` left join `growsystem`.`usuarios` `u` on(`d`.`usuario_id` = `u`.`id`)) left join `growsystem`.`instancias` `i` on(`d`.`id` = `i`.`dispositivo_id`)) left join `growsystem`.`api_tokens` `t` on(`d`.`id` = `t`.`dispositivo_id` and `t`.`activo` = 1))
md5=68ad6fe0750b16a7fa82a1fc5c15055c
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001775447410273328
create-version=2
source=SELECT `d`.`id` AS `id`, `d`.`numero_serie` AS `numero_serie`, `d`.`estado` AS `estado`, `d`.`fecha_creacion` AS `fecha_creacion`, `d`.`fecha_asignacion` AS `fecha_asignacion`, `u`.`nombre` AS `usuario_nombre`, `u`.`email` AS `usuario_email`, `i`.`alias` AS `instancia_alias`, `i`.`activa` AS `instancia_activa`, `t`.`token` AS `api_token`, `t`.`ultimo_uso` AS `token_ultimo_uso` FROM (((`dispositivos` `d` left join `usuarios` `u` on(`d`.`usuario_id` = `u`.`id`)) left join `instancias` `i` on(`d`.`id` = `i`.`dispositivo_id`)) left join `api_tokens` `t` on(`d`.`id` = `t`.`dispositivo_id` and `t`.`activo` = 1))
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `d`.`id` AS `id`,`d`.`numero_serie` AS `numero_serie`,`d`.`estado` AS `estado`,`d`.`fecha_creacion` AS `fecha_creacion`,`d`.`fecha_asignacion` AS `fecha_asignacion`,`u`.`nombre` AS `usuario_nombre`,`u`.`email` AS `usuario_email`,`i`.`alias` AS `instancia_alias`,`i`.`activa` AS `instancia_activa`,`t`.`token` AS `api_token`,`t`.`ultimo_uso` AS `token_ultimo_uso` from (((`growsystem`.`dispositivos` `d` left join `growsystem`.`usuarios` `u` on(`d`.`usuario_id` = `u`.`id`)) left join `growsystem`.`instancias` `i` on(`d`.`id` = `i`.`dispositivo_id`)) left join `growsystem`.`api_tokens` `t` on(`d`.`id` = `t`.`dispositivo_id` and `t`.`activo` = 1))
mariadb-version=100432
