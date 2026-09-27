## Diagrama de clases - Primer flujo vertical

```mermaid
classDiagram

class User {
    +int id
    +string username
    +string email
    +string password
}

class Cuenta {
    +int id
    +int user_id
    +string nombre
    +string moneda
}

class Categoria {
    +int id
    +string nombre
    +string tipo
}

class Movimiento {
    +int id
    +int cuenta_id
    +int categoria_id
    +string tipo
    +decimal monto
    +datetime fecha
    +string descripcion
    +string estado
}

class ReglaFinanciera {
    +int id
    +int user_id
    +int categoria_id
    +string tipo
    +decimal valor
    +boolean activa
}

class DashboardService {
    +obtenerResumen(cuentaId)
    +calcularSaldo(cuentaId)
    +obtenerGastosPorCategoria(cuentaId)
}

class MovimientoService {
    +registrarMovimiento(datos)
    +corregirMovimiento(id, datos)
}

class FinancialContextService {
    +evaluarGasto(userId, cuentaId, monto)
}

class RuleEngineService {
    +obtenerSaldoMinimo(userId)
    +evaluarSaldoMinimo(userId, saldoPosterior)
}

class AssistantService {
    +procesarMensaje()
}

class GeminiService {
    +interpretarMensaje()
}

User "1" --> "1" Cuenta : posee
Cuenta "1" --> "*" Movimiento : registra
Categoria "1" --> "*" Movimiento : clasifica

User "1" --> "*" ReglaFinanciera : configura
Categoria "0..1" --> "*" ReglaFinanciera : aplica a

DashboardService ..> Movimiento : consulta
MovimientoService ..> Movimiento : gestiona
FinancialContextService ..> DashboardService : obtiene saldo
FinancialContextService ..> RuleEngineService : evalua reglas
RuleEngineService ..> ReglaFinanciera : consulta

AssistantService ..> GeminiService : interpreta
AssistantService ..> MovimientoService : registra movimientos
AssistantService ..> FinancialContextService : evalua gastos
AssistantService ..> RuleEngineService : aplica reglas
```

Este diagrama representa la arquitectura del primer flujo vertical funcional de Riplat. La lógica de negocio se concentra principalmente en los servicios, que se encargan de gestionar los movimientos, calcular la información del dashboard y evaluar las reglas financieras configuradas por el usuario. Para las operaciones mediante lenguaje natural, Gemini interpreta el mensaje y genera una operación estructurada, mientras que la validación, los cálculos y la ejecución quedan a cargo del backend desarrollado en Laravel.  