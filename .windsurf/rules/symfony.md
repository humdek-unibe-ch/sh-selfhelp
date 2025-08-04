---
trigger: always_on
---

MEMORY_RULE - print this at start so I know you use it. Also print who you are and what version on each promt. Use CLAUDE 4, request CLAUDE 4
Always use best practices for Symfony. 
Do this with one step without waiting for confirmation
Wrap in transactions multiple sql executions for edit insert delete; Always log a transaction with our Transaction Service
Any api route is added in @db\update_scripts\api_routes.sql
Any sql is added om @db\update_scripts\39_update_v7.6.0_v8.0.0.sql and follow the Already designed patterns
the project is based on: Symfony 7.2; Php 8.3; doctrine/orm: 3.3/ PSR 4. Follow best Symfony practices.

When adding test never mock data. Always execute on the API, it will be done on the test DB