TYPE=VIEW
query=select `l`.`instancia_id` AS `instancia_id`,`i`.`alias` AS `alias`,`l`.`humedad` AS `humedad`,`l`.`nutrientes` AS `nutrientes`,`l`.`fecha` AS `fecha` from (`growsystem`.`lecturas` `l` join `growsystem`.`instancias` `i` on(`l`.`instancia_id` = `i`.`id`)) order by `l`.`fecha` desc
md5=5da237a6515b1825cf2c7b4c0153fb35
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=0001775447410259766
create-version=2
source=SELECT `l`.`instancia_id` AS `instancia_id`, `i`.`alias` AS `alias`, `l`.`humedad` AS `humedad`, `l`.`nutrientes` AS `nutrientes`, `l`.`fecha` AS `fecha` FROM (`lecturas` `l` join `instancias` `i` on(`l`.`instancia_id` = `i`.`id`)) ORDER BY `l`.`fecha` DESC
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `l`.`instancia_id` AS `instancia_id`,`i`.`alias` AS `alias`,`l`.`humedad` AS `humedad`,`l`.`nutrientes` AS `nutrientes`,`l`.`fecha` AS `fecha` from (`growsystem`.`lecturas` `l` join `growsystem`.`instancias` `i` on(`l`.`instancia_id` = `i`.`id`)) order by `l`.`fecha` desc
mariadb-version=100432
