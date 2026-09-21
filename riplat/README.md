## Diagrama de clases - Primer flujo vertical

```mermaid
classDiagram

    class User {
        +id
        +username
        +email
        +password
    }

    class Cuenta {
        +id
        +user_id
        +nombre
        +moneda
    }

    class Categoria {
        +id
        +nombre
        +tipo
    }

    class Movimiento {
        +id
        +cuenta_id
        +categoria_id
        +tipo
        +monto
        +fecha
        +descripcion
        +estado
    }

    class ReglaFinanciera {
        +id
        +user_id
        +categoria_id
        +tipo
        +valor
        +activa
    }

    class MovimientoController {
        +store()
        +update()
    }

    class MovimientoService {
        +registrarMovimiento()
        +corregirMovimiento()
    }

    class MovimientoRepository {
        +crear()
        +buscarPorId()
        +actualizar()
    }

    class DashboardController {
        +index()
    }

    class DashboardService {
        +obtenerResumen()
        +calcularSaldo()
    }

    class AssistantController {
        +procesar()
    }

    class AssistantService {
        +procesarMensaje()
        +ejecutarTool()
    }

    class GeminiService {
        +interpretar()
    }

    class FinancialContextService {
        +obtenerContexto()
        +evaluarGasto()
    }

    class RuleEngineService {
        +obtenerReglasActivas()
        +evaluarReglas()
    }

    User "1" --> "1" Cuenta : posee
    User "1" --> "*" ReglaFinanciera : configura

    Cuenta "1" --> "*" Movimiento : registra

    Categoria "1" --> "*" Movimiento : clasifica
    Categoria "1" --> "0..*" ReglaFinanciera : aplica

    MovimientoController --> MovimientoService
    MovimientoService --> MovimientoRepository
    MovimientoRepository --> Movimiento

    DashboardController --> DashboardService
    DashboardService --> MovimientoRepository

    AssistantController --> AssistantService
    AssistantService --> GeminiService
    AssistantService --> MovimientoService
    AssistantService --> FinancialContextService

    FinancialContextService --> MovimientoRepository
    FinancialContextService --> RuleEngineService
    RuleEngineService --> ReglaFinanciera
```

    Este diagrama representa la arquitectura inicial de Riplat para implementar el primer flujo vertical del sistema. La lógica de negocio se concentra en los servicios, mientras que los repositorios se encargan del acceso a los datos. Para el registro mediante lenguaje natural, el proveedor de IA interpreta el mensaje y genera una operación estructurada, pero la validación y ejecución quedan a cargo del backend en Laravel.