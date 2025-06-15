

Estructura de la tabla paginas
| column_name   | data_type                   | character_maximum_length | is_nullable | column_default                      |
| ------------- | --------------------------- | ------------------------ | ----------- | ----------------------------------- |
| id            | bigint                      | null                     | NO          | nextval('paginas_id_seq'::regclass) |
| titulo        | character varying           | 255                      | NO          | null                                |
| subtitulo     | character varying           | 255                      | YES         | null                                |
| contenido     | text                        | null                     | YES         | null                                |
| slug          | character varying           | 255                      | NO          | null                                |
| idautor       | bigint                      | null                     | YES         | null                                |
| estado        | character varying           | 50                       | NO          | 'borrador'::character varying       |
| tipocontenido | character varying           | 50                       | NO          | 'pagina'::character varying         |
| created_at    | timestamp without time zone | null                     | YES         | null                                |
| updated_at    | timestamp without time zone | null                     | YES         | null                                |


Estructura de la tabla de usuarios

| column_name       | data_type                | character_maximum_length | is_nullable | column_default                       |
| ----------------- | ------------------------ | ------------------------ | ----------- | ------------------------------------ |
| id                | integer                  | null                     | NO          | nextval('usuarios_id_seq'::regclass) |
| nombreusuario     | character varying        | 60                       | NO          | null                                 |
| correoelectronico | character varying        | 100                      | NO          | null                                 |
| clave             | character varying        | 255                      | NO          | null                                 |
| nombremostrado    | character varying        | 250                      | YES         | null                                 |
| fecharegistro     | timestamp with time zone | null                     | NO          | CURRENT_TIMESTAMP                    |
| rol               | character varying        | 50                       | NO          | 'suscriptor'::character varying      |
| remember_token    | character varying        | 100                      | YES         | null                                 |
| created_at        | timestamp with time zone | null                     | YES         | null                                 |
| updated_at        | timestamp with time zone | null                     | YES         | null                                 |

Estructura de opciones

| column_name   | data_type                   | character_maximum_length | is_nullable | column_default                       |
| ------------- | --------------------------- | ------------------------ | ----------- | ------------------------------------ |
| id            | integer                     | null                     | NO          | nextval('opciones_id_seq'::regclass) |
| opcion_nombre | character varying           | 191                      | NO          | null                                 |
| opcion_valor  | text                        | null                     | YES         | null                                 |
| created_at    | timestamp without time zone | null                     | YES         | null                                 |
| updated_at    | timestamp without time zone | null                     | YES         | null                                 |

Estructura de paginameta

| column_name | data_type         | character_maximum_length | is_nullable | column_default                              |
| ----------- | ----------------- | ------------------------ | ----------- | ------------------------------------------- |
| meta_id     | integer           | null                     | NO          | nextval('paginameta_meta_id_seq'::regclass) |
| pagina_id   | integer           | null                     | NO          | null                                        |
| meta_key    | character varying | 255                      | NO          | null                                        |
| meta_value  | text              | null                     | YES         | null                                        |