# 🐛 BugReportLite

**BugReportLite** é um plugin leve e eficiente para **PocketMine-MP (API 5.x)**, focado em servidores Survival/SMP. Ele permite que jogadores reportem falhas, bugs e erros através de uma interface visual (FormAPI) nativa, sem poluir o chat e com **zero impacto** na performance do servidor.

![PocketMine-MP](https://img.shields.io/badge/PocketMine-5.x-green?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)
![Performance](https://img.shields.io/badge/Performance-Async-orange?style=flat-square)

---

## ✨ Funcionalidades Principais

* **📝 Formulários Nativos:** Nada de comandos longos ou spam no chat. Tudo é feito via UI.
* **🚀 Zero Lag:** O salvamento de arquivos e envio de Webhooks é feito de forma **Assíncrona**, mantendo o TPS estável.
* **📂 Organização Diária:** Reports são salvos em arquivos JSON separados por dia (`2026-01-03.json`).
* **🤖 Integração com Discord:** Envia notificações automáticas para um canal do Discord via Webhook.
* **🛡️ Anti-Spam:** Sistema configurável de cooldown e limite diário de reports por jogador.
* **🔧 Ferramentas da Staff:** Comandos para listar reports recentes e teleportar diretamente para o local do bug.

---

## 🛠️ Comandos e Permissões

| Comando | Descrição | Permissão | Padrão |
| :--- | :--- | :--- | :--- |
| `/bug` | Abre o formulário para reportar um erro. | `bugreport.use` | Todos |
| `/buglist` | Lista os reports recebidos no dia atual. | `bugreport.staff` | OP |
| `/bugtp <id>` | Teleporta para o local exato de um report. | `bugreport.staff` | OP |

---
## 📸 Fluxo de Uso Detalhado

O **BugReportLite** foi projetado para ser intuitivo, minimizando erros de preenchimento e garantindo que a staff receba informações precisas (coordenadas e mundo) automaticamente, sem que o jogador precise digitá-las.

---

### 1. Início e Categorização
* **Ação:** O jogador digita o comando `/bug`.
* **Interface:** Um menu `SimpleForm` (botões) é exibido instantaneamente.
* **Objetivo:** Filtrar o problema antes da escrita. O jogador seleciona uma categoria:
    * 🗺️ **Terreno / Mapa**
    * 👾 **Mobs / Entidades**
    * 📦 **Item / Inventário**
    * ⚙️ **Plugin / Sistema**
    * ❓ **Outro**

### 2. Detalhamento (Input)
* **Ação:** Após selecionar a categoria, o sistema abre um `CustomForm`.
* **Interface:** Um campo de entrada de texto (Input).
* **Validação:** * O plugin verifica o tamanho mínimo configurado no `config.yml`.
    * Impede o envio de descrições vazias ou genéricas (ex: "ajuda", "bug").
    * Se for inválido, o jogador recebe um aviso e o processo é interrompido para correção.

### 3. Revisão e Coleta de Metadados
* **Interface:** Um `ModalForm` de confirmação final.
* **Dados Automáticos:** O sistema captura silenciosamente:
    * **Coordenadas exatas:** X, Y, Z.
    * **Mundo:** Nome do level/folder onde o bug ocorreu.
    * **Timestamp:** Data e hora exata do servidor.
* **Objetivo:** Mostrar ao jogador um resumo do que será enviado, permitindo a confirmação ou o cancelamento.

### 4. Processamento Assíncrono (Background)
* **Salvamento:** O report é serializado e enviado para uma `SaveReportTask`. A thread principal do servidor nunca é travada para operações de escrita em disco.
* **Notificação Discord:** Se o Webhook estiver ativo, uma `DiscordWebhookTask` envia um **Embed** formatado com cores para o canal da Staff no Discord.

### 5. Intervenção da Staff
* **Monitoramento:** A staff utiliza o comando `/buglist` para visualizar os IDs e resumos dos reports do dia.
* **Teleporte:** Ao identificar um report que precisa de investigação, o moderador utiliza `/bugtp <ID>`.
* **Resultado:** O moderador é teleportado instantaneamente para o local exato, facilitando a resolução do problema.

---
## 🔧 Requisitos Técnicos

Para garantir o funcionamento correto e a performance ideal do **BugReportLite**, certifique-se de que seu ambiente atende aos seguintes pré-requisitos:

---

### 🟢 Software de Servidor
* **PocketMine-MP:** API `5.0.0` ou superior (Compatível com as versões mais recentes do PM5).

### 🐘 Linguagem PHP
* **Versão do PHP:** `8.1` ou superior (Utiliza recursos modernos de tipagem e performance).

### 📦 Extensões e Dependências
* **JSON:** Obrigatória para o salvamento dos reports no disco.
* **cURL:** Recomendada para o envio de Webhooks ao Discord.
* **Stream Nativo:** O plugin possui um sistema de fallback nativo, funcionando mesmo em ambientes onde o cURL está desativado.
* **Dependências Externas:** **Nenhuma**. Este plugin é *standalone* e não requer `FormAPI` ou outras bibliotecas de terceiros para funcionar.

---



> **Nota:** Se você utiliza uma hospedagem (Host), verifique se as permissões de escrita de arquivos estão habilitadas na pasta do servidor para que os logs diários possam ser criados.

---
## 💾 Estrutura de Dados

O **BugReportLite** utiliza um sistema de armazenamento local baseado em arquivos JSON organizados cronologicamente. Isso facilita backups, auditorias manuais e garante que o plugin não sobrecarregue um único arquivo conforme o tempo passa.

### Localização dos Arquivos
Os reports são armazenados no seguinte diretório:
`📂 /plugin_data/BugReportLite/reports/`

### Organização dos Arquivos
Cada arquivo é nomeado seguindo o padrão de data ISO (`AAAA-MM-DD.json`), criando uma separação física por dia:
* `2026-01-06.json`
* `2026-01-07.json`
* `2026-01-08.json`

### Esquema do Objeto JSON
Dentro de cada arquivo, os reports são indexados pelo seu **ID Único**, permitindo acesso rápido via comandos da staff. Abaixo está o exemplo da estrutura interna:

```json
{
  "659b1a2": {
    "id": "659b1a2",
    "player": "Steve",
    "uuid": "jouuid-uuid-uuid-uuid",
    "type": "Terreno / Mapa",
    "description": "Buraco na bedrock no spawn",
    "world": "world",
    "x": 100,
    "y": 64,
    "z": 100,
    "timestamp": "14:30:00"
  }
}

---

## ⚙️ Instalação e Configuração

1.  Baixe o arquivo `.phar` ou compile o código fonte.
2.  Coloque na pasta `/plugins/` do seu servidor.
3.  Reinicie o servidor.
4.  Configure o arquivo `plugin_data/BugReportLite/config.yml` conforme necessário.

### Exemplo do `config.yml`

```yaml
# Configuração Principal
settings:
  # Tempo em segundos que o jogador deve esperar entre reports
  cooldown: 300
  # Máximo de reports que um jogador pode enviar por dia
  daily-limit: 5
  # Limites de caracteres para a descrição
  desc-min-length: 10
  desc-max-length: 200

# Integração com Discord
discord:
  enabled: false # Mude para true para ativar
  webhook-url: "[https://discord.com/api/webhooks/SEU_WEBHOOK_AQUI](https://discord.com/api/webhooks/SEU_WEBHOOK_AQUI)"
  username: "BugReport Bot"

# Mensagens (Suporta cores com §)
messages:
  prefix: "§l§6[BugReport] §r"
  success: "§aSeu report foi enviado com sucesso! A staff agradece."
  # ... (outras mensagens configuráveis)
