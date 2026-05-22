# MiTemplate

MiTemplate é um mecanismo de templates **leve e moderno escrito em PHP**, focado em **simplicidade**, **performance** e **controle explícito por código**.

Ele permite separar HTML da lógica da aplicação sem depender de parsers complexos, ASTs, engines pesadas ou fases de compilação.

O template é interpretado de forma **incremental e determinística**, com controle total da renderização pelo código PHP.

MiTemplate é baseado no [MGTemplate](https://github.com/bluiceoficial/mgtemplate).

---

## ✨ Características

* Interpolação simples de variáveis: `{{title}}`
* Suporte a objetos e arrays: `{{user.name}}`
* Modificadores encadeáveis: `{{title|upper|trim}}`
* Seções reutilizáveis com repetição controlada por código
* Seções aninhadas com resolução tardia (*lazy sections*)
* Limpeza automática de seções e variáveis não utilizadas
* Sem uso de Reflection insegura (`setAccessible`)
* Compatível com **PHP 8.4 ou superior**
* Ideal para APIs, sites, CLIs e projetos embarcados

---

## 📦 Instalação

### Via Composer (recomendado)

```bash
composer require mugomes/mitemplate
```

### Manual

Copie o arquivo `MiTemplate.php` para o seu projeto e faça a inclusão.

---

## 🚀 Uso básico

### Template (`template.html`)

```html
<!DOCTYPE html>
<html>
<head>
    <title>{{title}}</title>
</head>
<body>

<h1>{{title|upper}}</h1>

[[item]]
<div>
    <strong>{{user.name}}</strong><br>
    <small>{{info}}</small>
</div>
[[/item]]

</body>
</html>
```

---

### Código PHP

```php
<?php

use MiTemplate\MiTemplate;

$tpl = new MiTemplate('template.html');

$tpl->var('title', 'MiTemplate Example');

for ($i = 0; $i < 5; $i++) {
    $tpl->var('user', (object)[
        'name' => 'Usuário ' . $i,
    ]);

    $tpl->var('info', 'Informação ' . $i);
    $tpl->section('item');
}

echo $tpl->render();
```

---

## 🧩 Seções

Seções são definidos diretamente no HTML:

```html
[[item]]
<p>{{user.name}}</p>
[[/item]]
```

E ativados no código sempre que necessário:

```php
$tpl->section('item');
```

Cada chamada adiciona uma nova instância renderizada da seção.

Caso precise trabalhar com seções em loop é necessário ativá-lo:

```php
for ($i = 0; $i < 5; $i++) {
    $tpl->var('i', $i);
    $tpl->section('item', true);
}
```

### ✔ Ordem livre

É possível chamar seções filhos **antes** das seções pais:

```php
for ($i = 0; $i < 3; $i++) {
    $tpl->var('itemNome', $i);
    $tpl->section('item');
}

$tpl->section('conteudo');
```

O MiTemplate resolve toda a hierarquia automaticamente durante o `render()`.

---

## 🔧 Variáveis

### Variável simples

```html
{{title}}
```

```php
$tpl->var('title', 'Exemplo');
echo $tpl->getVar('title');
```

### Verificar se a variável existe no template

```php
if ($tpl->varExists('title')) {
    $tpl->var('title', 'Novo título');
}
```

---

### Objetos e Arrays

```html
{{user.name}}
{{user.email}}
```

```php
$tpl->var('user', (object)[
    'name'  => 'Murilo',
    'email' => 'murilo@email.com'
]);
```

* Acesso **case-insensitive**
* Ignora `_` e diferenças de nomenclatura
* Suporta getters (`getName()`)

---

## 📂 Incluir outros arquivos HTML

```html
{{menuTopo}}
```

```php
$tpl->includeFile('menuTopo', 'partials/menu.html');
```

---

## 🔁 Modificadores

Modificadores podem ser encadeados usando `|`.

| Modificador | Descrição                |
| ----------- | ------------------------ |
| `upper`     | Converte para maiúsculas |
| `lower`     | Converte para minúsculas |
| `trim`      | Remove espaços           |

Exemplo:

```html
{{title|upper|trim}}
```

---

## 🧹 Limpeza automática

Durante o `render()`, o MiTemplate remove automaticamente:

* Seções não utilizadas:

  ```
  [[section]] ... [[/section]]
  ```
* Tags órfãs:

  ```
  [[/section]]
  ```
* Variáveis não resolvidas:

  ```
  {{variavel}}
  ```

Isso garante **HTML limpo e válido**, mesmo quando as seções não são ativadas.

---

## 👤 Autor

**Murilo Gomes Julio**

🔗 [https://www.bluice.com.br](https://www.bluice.com.br)

📺 [https://youtube.com/@mugomesoficial](https://youtube.com/@mugomesoficial)

---

## 🤝 Support

* GitHub Sponsors: [https://github.com/sponsors/mugomes](https://github.com/sponsors/mugomes)
* Apoie o projeto: [https://www.bluice.com.br/apoie/](https://www.bluice.com.br/apoie/)

---

## 📜 License

Copyright (c) 2025-2026 Murilo Gomes Julio

Licensed under the [MIT](https://github.com/bluiceoficial/mitemplate/blob/main/LICENSE).

All contributions to the MiTemplate are subject to this license.
