Categorías (categories)
- id (int, primary key)
- name (string, unique) - Nombre de la categoría
- icon (string) - Nombre del icono asociado a la categoría
- slug (string, unique) - Slug para la URL de la categoría

Modelos (models)
- id (int, primary key)
- name (string, unique) - Nombre del modelo
- icon (string) - Nombre del icono asociado al modelo
- description (text) - Descripción del modelo
- slug (string, unique) - Slug para la URL del modelo

Usuarios (users)
- id (int, primary key)
- email (string, unique) - Correo electrónico del usuario
- name (string, unique) - Nombre de usuario
- password (string) - Contraseña cifrada
- role (string) - Rol del usuario (admin o user)
- created_at (timestamp)
- updated_at (timestamp)

Informacion de usuario (user_data)
- user_id (int, primary key)
- icon (string) - Nombre del icono del usuario
- bio (text) - Resumen sobre el usuario
- full_name (string) - Apellido y Nombres del usuario
- address (text) - Domicilio del usuario
- tax_id (string) - Clave Tributaria (CUIT/CUIL/CDI, número de documento de identidad, Pasaporte, CI, etc.)
- bank_key (string) - Clave Bancaria (CBU o CVU para recibir pagos)
- created_at (timestamp)
- updated_at (timestamp)

Prompts (prompts)
- id (int, primary key)
- title (string) - Título del prompt
- description (text) - Descripción del prompt
- prompt_content (text) - Texto del prompt
- example_response (text) - Ejemplo de respuesta del modelo
- user_id (int, foreign key) - Relación con el usuario que creó el prompt
- category_id (int, foreign key) - Relación con la categoría del prompt
- model_id (int, foreign key) - Relación con el modelo del prompt
- price (int) - Precio unitario del prompt
- created_at (timestamp)
- updated_at (timestamp)

Prompts Comprados (purchased_prompts)
- id (int, primary key)
- user_id (int, foreign key) - Relación con el usuario que compró el prompt
- prompt_id (int, foreign key) - Relación con el prompt comprado
- created_at (timestamp)
- updated_at (timestamp)