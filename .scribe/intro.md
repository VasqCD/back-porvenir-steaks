# Introduction

API REST para el sistema de pedidos de El Porvenir Steaks, incluyendo gestión de usuarios, productos, categorías, pedidos, repartidores y más.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>

    # Bienvenido a la API de El Porvenir Steaks
    Esta documentación proporciona toda la información necesaria para trabajar con nuestra API REST.

    ## Descripción general
    El Porvenir Steaks es un sistema de pedidos en línea que permite a los clientes explorar el menú,
    realizar pedidos y seguir su estado en tiempo real. También facilita a los administradores
    gestionar productos, categorías y repartidores, mientras que los repartidores pueden
    actualizar estados de pedidos y su disponibilidad.

    ## Autenticación
    La API utiliza autenticación basada en tokens mediante Laravel Sanctum. Para acceder a los endpoints
    protegidos, debes incluir tu token en el header `Authorization: Bearer {token}`.

    ## Roles y permisos
    El sistema cuenta con tres roles principales:

    - **Cliente**: Puede ver productos, realizar pedidos y gestionar sus ubicaciones
    - **Repartidor**: Puede actualizar estados de pedidos y su disponibilidad
    - **Administrador**: Tiene acceso completo a todas las funcionalidades

    ## Modelos principales

    - **User**: Representa usuarios (clientes, repartidores, administradores)
    - **Producto**: Artículos disponibles para venta
    - **Categoría**: Agrupaciones de productos
    - **Pedido**: Órdenes realizadas por los clientes
    - **DetallePedido**: Productos individuales dentro de un pedido
    - **Ubicación**: Direcciones de entrega
    - **Repartidor**: Información específica de los usuarios con rol repartidor
    - **Notificación**: Mensajes enviados a los usuarios

