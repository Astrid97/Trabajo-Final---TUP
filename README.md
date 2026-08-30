<img width="1414" height="2000" alt="Texto del párrafo (1)" src="https://github.com/user-attachments/assets/444251a6-ebeb-475f-a5b4-9d722a575f57" />

---

##  Tabla de Contenidos

- [Riplat — Gestión Financiera Personal con Asistente IA](#riplat--gestión-financiera-personal-con-asistente-ia)
  - [Tabla de Contenidos](#tabla-de-contenidos)
  - [1. Descripción del Proyecto](#1-descripción-del-proyecto)
    - [Características Principales:](#características-principales)
  - [2. Justificación y Validación Empírica](#2-justificación-y-validación-empírica)
    - [Análisis de la Competencia y Oportunidad](#análisis-de-la-competencia-y-oportunidad)
    - [Validación con Potenciales Usuarios ($N = 87$)](#validación-con-potenciales-usuarios-n--87)
  - [3. Objetivos](#3-objetivos)
    - [Objetivo General](#objetivo-general)
    - [Objetivos Específicos](#objetivos-específicos)
  - [4. Alcance del Proyecto](#4-alcance-del-proyecto)
    - [Funcionalidades del MVP](#funcionalidades-del-mvp)
    - [Fuera de Alcance (Limitaciones Explícitas)](#fuera-de-alcance-limitaciones-explícitas)
  - [5. Identificación de Interesados (Stakeholders)](#5-identificación-de-interesados-stakeholders)
  - [6. Cronograma Estimado (Planificación)](#6-cronograma-estimado-planificación)
  - [7. Recursos Necesarios (Stack Tecnológico y Equipo)](#7-recursos-necesarios-stack-tecnológico-y-equipo)
    - [Stack Tecnológico](#stack-tecnológico)
  - [8. Identificación de Riesgos y Mitigación](#8-identificación-de-riesgos-y-mitigación)
  - [9. Aspectos Legales y de Privacidad](#9-aspectos-legales-y-de-privacidad)
  - [10. Anexos](#10-anexos)

---

## 1. Descripción del Proyecto

**Riplat** es una plataforma orientada a simplificar el registro, organización y consulta de la información financiera personal mediante una interfaz de baja fricción, utilizando un **asistente conversacional** como mecanismo principal de interacción.

<img width="2423" height="2048" alt="Comp Mokups" src="https://github.com/user-attachments/assets/b18f4de1-803b-49a5-aa8d-a0c5476f2aa1" />

### Características Principales:
- **Registro por Lenguaje Natural:** Centralización de ingresos (diversas fuentes), gastos y reglas personales de administración mediante mensajes simples de chat.
- **Dashboard Financiero:** Visualización clara de balance disponible, ingresos vs. gastos, distribución por categorías y evolución histórica.
- **Asistente IA con Function Calling:** El LLM interpreta la intención del usuario y solicita operaciones estructuradas. **Laravel** actúa como orquestador, validando datos, ejecutando cálculos y garantizando la consistencia financiera.
- **Evaluación de Viabilidad de Gastos:** Consulta al asistente sobre el impacto de compras futuras en función de reglas personalizadas (ahorro mínimo, topes por categoría, etc.).
- **Arquitectura Offline-First:** Soporte mediante *Service Workers* y almacenamiento local (*LocalStorage*). Los mensajes sin conexión se encolan con identificadores únicos y se procesan automáticamente al recuperar conectividad.

---

## 2. Justificación y Validación Empírica

### Análisis de la Competencia y Oportunidad
Riplat elimina la fricción de los formularios tradicionales combinando el registro conversacional en lenguaje natural con la toma de decisiones informada y alertas en tiempo real.

### Validación con Potenciales Usuarios ($N = 87$)
A partir de un relevamiento empírico se obtuvieron los siguientes resultados:

| Métrica / Hallazgo | Resultado | Impacto en el Diseño |
| :--- | :---: | :--- |
| **Falta de herramientas de control** | **36,8%** | No utiliza ninguna herramienta formal para el control de gastos. |
| **Dificultad en toma de decisiones** | **74,7%** | Tuvo que calcular manualmente si disponía de saldo antes de un gasto. |
| **Utilidad de evaluación de gastos** | **4,09 / 5** | Alta valoración de la consulta preventiva de impacto en el balance. |
| **Intención de adopción** | **83,9%** | Intención de uso positiva (27,6% seguro / 56,3% probable). |
| **Privacidad de la información** | **Prioritaria** | Requerimiento clave: arquitectura con anonimización y minimización de datos. |

---

## 3. Objetivos

### Objetivo General
Desarrollar e implementar una **Aplicación Web Progresiva (PWA)** de gestión financiera personal en **Laravel (PHP)** con arquitectura en capas (Controladores, Servicios, Repositorios y Modelos) y base de datos relacional (**SQL**). El sistema integrará un asistente conversacional con IA mediante *Function Calling*, un dashboard financiero y evaluación preventiva de gastos bajo reglas configurables, entregando un **MVP funcional en un plazo máximo de 2 meses**.

### Objetivos Específicos
1. **Arquitectura y Persistencia:** Modelar una base de datos relacional con integridad ACID y patrón *Controller-Service-Repository-Model*.
2. **Registro Conversacional:** Transformar lenguaje natural no estructurado en transacciones validadas en base de datos.
3. **Orquestación Segura de IA:** Implementar *Function Calling* donde Laravel valide parámetros y realice los cálculos, evitando delegar operaciones críticas al modelo.
4. **Clasificación Automática:** Categorizar movimientos automáticamente a partir del contexto del mensaje.
5. **Dashboard Visual:** Exponer métricas de saldo disponible, gastos por categoría y evolución temporal.
6. **Motor de Viabilidad y Reglas:** Evaluar compras contra criterios de ahorro, saldo mínimo y límites configurados.
7. **Resiliencia Offline-First:** Implementar almacenamiento local y sincronización asíncrona mediante Service Workers.
8. **Diseño Mobile-First:** Priorizar la usabilidad en dispositivos móviles optimizando tiempos de carga y flujos de interacción.
9. **Privacidad por Diseño:** Minimizar datos personales y anonimizar los prompts enviados a servicios externos.

---

## 4. Alcance del Proyecto

### Funcionalidades del MVP

```
┌────────────────────────────────────────────────────────────────────────┐
│                              RIPLAT (MVP)                              │
├──────────────────┬──────────────────┬──────────────────┬───────────────┤
│    GESTIÓN &     │     REGISTRO     │     ANÁLISIS     │ RESILIENCIA & │
│    SEGURIDAD     │  CONVERSACIONAL  │   & DECISIÓN     │   OFFLINE     │
├──────────────────┼──────────────────┼──────────────────┼───────────────┤
│ • Autenticación  │ • Chat con IA    │ • Dashboard PWA  │ • Service     │
│ • Cuentas Indiv. │ • Multifuente    │ • Viabilidad     │   Workers     │
│ • Minimización   │ • Auto-categoría │ • Reglas/Alertas │ • Cola Local  │
└──────────────────┴──────────────────┴──────────────────┴───────────────┘
```

- **Gestión de Usuarios:** Registro, inicio de sesión seguro y aislamiento de información por cuenta.
- **Registro Conversacional:** Ingreso de transacciones exclusivamente vía chat en lenguaje natural.
- **Clasificación Automática:** Asignación contextual de categorías a cada gasto o ingreso.
- **Gestión Multifuente:** Soporte para múltiples fuentes de ingreso (fijas y variables).
- **Dashboard Financiero:** Panel de control interactivo con indicadores y gráficos.
- **Asistente de Consultas:** Consultas de balances, resúmenes periódicos e historial mediante lenguaje natural.
- **Evaluación Preventiva de Gastos:** Respuestas comprensibles sobre el impacto de un potencial gasto.
- **Reglas y Alertas Configurables:** Parámetros personalizados (saldo mínimo, límites y presupuestos).
- **Modo Offline-First:** Encolado local de mensajes y sincronización al reanudar conexión.
- **PWA Mobile-First:** Experiencia optimizada para navegadores móviles e instalación web.

### Fuera de Alcance (Limitaciones Explícitas)

- Formularios tradicionales de carga manual (la interacción es puramente conversacional).
- Integración directa / scraping de entidades bancarias o billeteras virtuales.
-  Valuación y administración de inventario o activos físicos (inmuebles, metales, stock).
- Reconocimiento óptico de caracteres (OCR) para tickets o facturas físicas.
- Modelos predictivos de Machine Learning para proyecciones futuras complejas.
- Asesoramiento financiero o de inversiones certificado.
- Edición o eliminación de movimientos preexistentes sin conexión a internet.
- Cotizaciones en tiempo real de activos bursátiles o criptomonedas.
- Aplicaciones móviles nativas (iOS / Android nativo).
- Gestión colaborativa o cuentas multiusuario compartidas.

---

## 5. Identificación de Interesados (Stakeholders)

```
                       ┌─────────────────────────┐
                       │  Cátedra & Evaluadores  │  (Alto Poder / Interés Normativo)
                       │        UTN - TUP        │
                       └────────────┬────────────┘
                                    │
                                    ▼
┌─────────────────────────┐   ┌───────────┐   ┌─────────────────────────┐
│      Tutor Técnico      │──▶│  RIPLAT   │◀──│  Equipo de Desarrollo   │
│     (Oscar Londero)     │   │  PROJECT  │   │(Astrid, Carina, Micaela)│
└─────────────────────────┘   └─────┬─────┘   └─────────────────────────┘
                                    │
                                    ▼
                       ┌─────────────────────────┐
                       │    Usuarios Finales     │  (Validación & Adopción)
                       │ (Muestra N=87 / PFM)    │
                       └─────────────────────────┘
```

- **Equipo de Desarrollo:** Astrid, Carina y Micaela (Ejecución, arquitectura, frontend, backend y QA).
- **Cátedra Evaluadora (UTN):** Evaluación académica, validación del stack tecnológico (PHP/Laravel/SQL) y cumplimiento del plan de trabajo.
- **Tutor Técnico (Oscar Londero):** Consultor en IA y Full Stack; guía en la arquitectura del agente híbrido, *Tool Calling* y optimización de base de datos.
- **Usuarios Finales:** Administradores de finanzas personales y trabajadores independientes que validan la utilidad y experiencia de usuario.

---

## 6. Cronograma Estimado (Planificación)

El proyecto se ejecuta en un período de **8 semanas (2 meses)** mediante sprints semanales:

```
Semana 1  [████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░] Arquitectura Base & SQL
Semana 2  [████████████░░░░░░░░░░░░░░░░░░░░░░░░] Usuarios & Núcleo Financiero
Semana 3  [████████████████░░░░░░░░░░░░░░░░░░░░] API REST & Dashboard
Semana 4  [████████████████████░░░░░░░░░░░░░░░░] Integración IA & Chat
Semana 5  [████████████████████████░░░░░░░░░░░░] Function Calling & Validaciones
Semana 6  [████████████████████████████░░░░░░░░] Reglas, Viabilidad & Offline-First
Semana 7  [████████████████████████████████░░░░] QA, Pruebas Integrales & Seguridad
Semana 8  [████████████████████████████████████] Despliegue, Documentación & Defensa
```

- **Semana 1:** Configuración de repositorios, entorno, estructura *Controller-Service-Repository-Model* y migraciones SQL.
- **Semana 2:** Autenticación, lógica transaccional de ingresos/gastos/categorías y maquetado base.
- **Semana 3:** Endpoints REST, agregaciones para balances y dashboard funcional.
- **Semana 4:** Conexión con proveedor de IA, interfaz de chat y procesamiento de lenguaje natural.
- **Semana 5:** Definición de herramientas (*Tool Use*), validaciones de parámetros en backend y clasificación automática.
- **Semana 6:** Motor de reglas financieras, evaluación preventiva de viabilidad, Service Workers y sincronización de mensajes pendientes.
- **Semana 7:** Pruebas integrales (lógica contable, IA, sincronización offline y seguridad).
- **Semana 8:** Despliegue en producción, documentación final, manual de usuario y preparación de defensa técnica.

---

## 7. Recursos Necesarios (Stack Tecnológico y Equipo)

### Stack Tecnológico
- **Frontend (PWA):** HTML5, CSS3, TypeScript, Service Workers y LocalStorage API.
- **Backend (API):** PHP con **Laravel** (arquitectura en capas: Controllers, Services, Repositories, Models).
- **Persistencia:** Base de datos relacional **SQL** (MySQL / PostgreSQL) mediante Eloquent ORM con transacciones ACID.
- **Inteligencia Artificial:** Modelos LLM integrados vía **OpenRouter** con soporte para *Function Calling* / *Tool Use*.
- **Infraestructura:** Cloud PaaS para backend/SQL y CDN estática (Vercel / Netlify) para el frontend PWA.
- **Gestión:** Git, GitHub y tableros Kanban (Jira / Trello).

---

## 8. Identificación de Riesgos y Mitigación

| Tipo | Riesgo Identificado | Impacto | Estrategia de Mitigación |
| :--- | :--- | :---: | :--- |
| **Técnico** | Inconsistencia o duplicación de datos en sincronización offline. | **Alto** | Asignación de UUID en local; Laravel procesa de forma idempotente y descarta duplicados antes de limpiar la cola local. |
| **Técnico** | Alucinaciones algorítmicas o cálculos erróneos en el LLM. | **Alto** | La IA solo extrae intenciones (*Function Calling*). Las reglas contables, cálculos y validaciones son 100% ejecutados por Laravel. |
| **Operativo** | Curva de aprendizaje en PHP / Laravel. | **Medio** | Mocking de datos en frontend para evitar bloqueos y escalamiento al tutor técnico ante trabas > 48h. |
| **Operativo** | Expansión del alcance (*Scope Creep*). | **Crítico** | Apego estricto a las limitaciones del MVP. Nuevas ideas se derivan a backlog de versiones futuras. |

---

## 9. Aspectos Legales y de Privacidad

- **Minimización de Datos (Data Minimization):** Cumplimiento con la **Ley 25.326** (Protección de Datos Personales). Solo se requiere correo y *nickname*; no se almacenan DNI, nombres reales ni datos bancarios.
- **Privacidad en IA (Prompt Sanitization):** Laravel filtra la información antes de enviarla a OpenRouter. Se priorizan proveedores con directivas *Zero Data Retention (ZDR)*.
- **Disclaimer Legal:** La plataforma no constituye una entidad financiera ni se encuentra regulada por el BCRA. El asistente ofrece apoyo analítico y no asesoramiento financiero profesional vinculante.

---

## 10. Anexos

- **Código Fuente:** [Repositorio del proyecto en GitHub](#)
- **Datos de Campo:** [Respuestas de la Encuesta (Hoja de Cálculo)](#)
- **Notebook de Análisis:** [Análisis Exploratorio de Datos (Google Colab)](#)
