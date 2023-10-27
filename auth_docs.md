**1. Registro y Autenticación:**

- **POST: http://localhost:8888/api/register**
  - Esta ruta permite a los usuarios registrarse en la aplicación. El usuario debe proporcionar un nombre, correo electrónico, contraseña y un secreto (JWT_SECRET).
  - El usuario se almacena en la base de datos con un rol predeterminado de "user".

- **POST: http://localhost:8888/api/login**
  - Permite a los usuarios autenticarse proporcionando su correo electrónico y contraseña.
  - Si las credenciales son válidas, se emite un token JWT como respuesta.

- **POST: http://localhost:8888/api/refresh**
  - Renueva el token JWT de un usuario autenticado. Es útil para mantener la sesión activa.

**2. Recursos de Elementos:**

- **GET: http://localhost:8888/api/items**
  - Recupera todos los elementos en la base de datos. Disponible para todos los usuarios autenticados, sin importar el rol.

- **GET: http://localhost:8888/api/items/{id}**
  - Recupera un elemento específico por su ID.

- **POST: http://localhost:8888/api/items**
  - Crea un nuevo elemento en la base de datos. Sin embargo, esta ruta está protegida por un middleware de rol, lo que significa que solo los usuarios con el rol "admin" pueden acceder a ella.

- **PUT: http://localhost:8888/api/items/{id}**
  - Actualiza un elemento específico en la base de datos. Al igual que la ruta de creación, está protegida por un middleware de rol "admin".

- **DELETE: http://localhost:8888/api/items/{id}**
  - Elimina un elemento específico en la base de datos. También protegido por el middleware de rol "admin".

**3. Middlewares:**

- **`Authenticate` Middleware:** Este middleware se aplica a rutas que requieren autenticación. Comprueba si el usuario está autenticado y si no lo está, devuelve una respuesta de "No autorizado".

- **`RoleMiddleware` Middleware:** Este middleware se utiliza para restringir el acceso a rutas basadas en roles. Si el usuario no tiene el rol adecuado, se le niega el acceso.

**4. Roles de Usuario:**

Los roles de usuario se definen en la base de datos a través del campo "role" en la tabla de usuarios. Por defecto, cuando se registra un usuario, su rol se establece en "user". Sin embargo, a través de la ruta de registro, es posible establecer un rol diferente si se proporciona el campo "role" en la solicitud.

Por ejemplo, si un usuario se registra como administrador con el siguiente registro:

```json
POST: http://localhost:8888/api/register
BODY: {
    "name":"cuantica",
    "email":"admin@cuantica.com",
    "password":"235711",
    "secret":"FsJJgQTOULLtVAn7J2Uxl6MNVhyif6Aq72hI7Ddt5J8yYqRzhgmTT4jWb8hYgzN6",
    "role":"admin"
}
```

Este usuario se registrará como administrador y tendrá acceso a las rutas protegidas por el middleware de rol "admin".

Por otro lado, si un usuario se registra sin especificar el campo "role" o con un valor diferente a "admin", se registrará como usuario normal y solo tendrá acceso a las rutas no protegidas por el middleware de rol.

**5. Token JWT:**

El sistema utiliza tokens JWT para la autenticación de usuarios. Cuando un usuario inicia sesión correctamente, se emite un token JWT que debe incluirse en el encabezado de las solicitudes subsiguientes con la clave "Authorization". El servidor verifica la validez del token y, si es válido, permite el acceso a las rutas protegidas.

En resumen, el sistema implementa una autenticación segura con roles de usuario y protección de rutas mediante middlewares. Los usuarios pueden registrarse, iniciar sesión y realizar operaciones de CRUD en elementos, con restricciones de rol para ciertas operaciones.

**1. Autenticación y Roles de Usuario:**

- Cuando un usuario se registra en el sistema, se crea una entrada en la tabla de usuarios de la base de datos. Cada usuario tiene un campo llamado "role" que se establece en "user" de forma predeterminada al registrarse.

- Un usuario puede registrarse con un rol diferente proporcionando el campo "role" en la solicitud de registro. Esto se usa para distinguir entre usuarios normales y administradores, pero el sistema podría ampliarse para incluir más roles según las necesidades.

- Los usuarios se autentican proporcionando su correo electrónico y contraseña mediante la solicitud POST a `/api/login`. Si las credenciales son correctas, el sistema genera un token JWT y lo devuelve al usuario como parte de la respuesta.

**2. Generación y Validación de Tokens JWT:**

- Los tokens JWT son piezas de información codificadas que contienen datos del usuario y se utilizan para verificar la identidad de un usuario en cada solicitud subsiguiente.

- El token JWT es generado por el sistema cuando el usuario se autentica. Contiene información del usuario, como su ID y el rol. El sistema utiliza la biblioteca Tymon\JWTAuth para generar y validar estos tokens de forma segura.

- Cada token tiene un período de validez, después del cual el usuario debe volver a autenticarse. El sistema configura esta expiración en el archivo de configuración (normalmente 24 horas, pero se puede ajustar).

**3. Protección de Rutas y Middleware:**

- Para proteger ciertas rutas o recursos, se utilizan middlewares. Un middleware es una capa intermedia que intercepta las solicitudes antes de llegar al controlador correspondiente. Esto permite realizar comprobaciones y tomar decisiones sobre si se debe permitir o denegar el acceso a la ruta.

- En el sistema, se han definido dos middlewares clave:

  - **`Authenticate` Middleware:** Este middleware se aplica a las rutas que requieren autenticación. Comprueba si el usuario ha proporcionado un token JWT válido en el encabezado de la solicitud. Si el token no es válido o está ausente, se devuelve una respuesta de "No autorizado" (código 401).

  - **`RoleMiddleware` Middleware:** Este middleware se utiliza para verificar los roles de usuario y restringir el acceso a ciertas rutas. Por ejemplo, en la ruta POST `/api/items`, se aplica el middleware `role:admin`, lo que significa que solo los usuarios con el rol "admin" pueden acceder a ella. Si el usuario tiene un rol diferente, se le deniega el acceso con una respuesta de "No tienes permiso para acceder a esta ruta" (código 403).

**4. Validación de Roles con el Token JWT:**

- Para saber el rol de un usuario en una solicitud, el sistema utiliza la información contenida en el token JWT proporcionado en el encabezado de la solicitud (normalmente en el encabezado "Authorization").

- El token JWT contiene un payload (carga) que incluye información del usuario, como el ID, el correo electrónico, el rol, etc. Cuando el middleware `RoleMiddleware` intercepta una solicitud, extrae esta información del token JWT y verifica si coincide con el rol requerido para la ruta. Si no coincide, se niega el acceso.

- Para simplificar, el token JWT es como una credencial que el usuario lleva consigo en cada solicitud. El sistema utiliza esta credencial para verificar quién es el usuario y qué permisos tiene para acceder a rutas específicas.

En resumen, el sistema utiliza tokens JWT para la autenticación y middleware para la protección de rutas basada en roles. Los tokens JWT llevan información del usuario, incluido su rol, que se utiliza para tomar decisiones sobre el acceso a recursos protegidos. El sistema es robusto y seguro, asegurando que solo los usuarios autenticados y con los roles adecuados puedan acceder a las rutas especificadas.