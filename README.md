# jmf/crud-engine-bundle

A Symfony bundle that automates CRUD (Create, Read, Update, Delete) controllers and routes for Doctrine entities based on configuration, eliminating repetitive boilerplate code.

## Requirements

- PHP 8.3+
- Symfony 7.0 or 8.0
- Doctrine ORM 3.0+

## Installation

```bash
composer require jmf/crud-engine-bundle
```

Register the bundle in `config/bundles.php` if not using Symfony Flex:

```php
return [
    // ...
    Jmf\CrudEngine\JmfCrudEngineBundle::class => ['all' => true],
];
```

## Quick Start

### 1. Configure the bundle

Create `config/packages/jmf_crud_engine.yaml`:

```yaml
jmf_crud_engine:
    entities:
        App\Entity\Article:
            actions:
                create:
                    redirection:
                        route: article.index
                delete:
                    redirection:
                        route: dashboard
                index:
                read:
                update:
                    redirection:
                        route: article.index
```

### 2. Load the routes

In `config/routes.yaml`:

```yaml
jmf_crud_engine:
    resource: 'Jmf\CrudEngine\Routing\RouteLoader'
    type: service
```

### 3. Create templates (optional)

Out of the box, any action whose template is missing renders a built-in **bare** template, so the bundle works immediately. To customize a view, create a Twig template at the configured path (e.g., `templates/article/index.html.twig`, `templates/article/create.html.twig`, etc.) and it takes precedence automatically.

Prefer to fail loudly instead of falling back? Set `schema.view.fallback: fail` (see [Missing templates](#missing-templates)).

That's it — the bundle automatically registers routes and wires up controllers for all configured actions.

## Actions

The bundle provides five actions, each mapped to a controller:

| Action     | Default Route Path      | HTTP Methods | Form | Description                        |
|------------|-------------------------|--------------|------|------------------------------------|
| `index`    | `/articles`             | GET          | No   | Lists all entities                 |
| `read`     | `/articles/{id}`        | GET          | No   | Displays a single entity           |
| `create`   | `/articles/new`         | GET, POST    | Yes  | Creates a new entity               |
| `update`   | `/articles/{id}/edit`   | GET, POST    | Yes  | Updates an existing entity         |
| `delete`   | `/articles/{id}/delete` | GET, POST    | No   | Deletes an entity (POST confirms)  |

Route paths and names are derived from the entity class name by default and can be overridden via configuration.

## Configuration Reference

```yaml
jmf_crud_engine:
    schema:
        # Default patterns for auto-discovering helper classes (Twig-style placeholders)
        helper:
            - "App\\Controller\\{{ EntityKey }}\\{{ ActionKey }}ActionHelper"
            - "App\\Controller\\{{ EntityKey }}{{ ActionKey }}ActionHelper"

        form:
            # Default patterns for auto-discovering form types
            type:
                - "App\\Form\\{{ EntityKey }}\\{{ ActionKey }}Type"
                - "App\\Form\\{{ EntityKey }}{{ ActionKey }}Type"
                - "App\\Form\\{{ EntityKey }}Type"

            # What to do when no form type is configured or discovered:
            #   generic (default) -> build a generic form from the entity's Doctrine metadata
            #   fail              -> throw an exception
            fallback: generic

        # Default view path pattern
        view:
            path: "{{ entity_key }}/{{ action_key }}.html.twig"

            # What to do when a view template is not found:
            #   built_in (default) -> render the bundle's built-in bare template
            #   fail               -> throw an exception
            fallback: built_in

    entities:
        App\Entity\Aricle:
            # Roles required for all actions on this entity (optional)
            roles:
                - ROLE_ADMIN

            actions:
                index: ~

                read: ~

                create:
                    # Override the form type (optional)
                    form:
                        type: App\Form\Article\CreateType

                    # Override the helper service (optional)
                    helper: App\Controller\Article\CreateActionHelper

                    # Redirect after a successful form submission (required for create/update/delete)
                    redirection:
                        route: article.index
                        parameters:
                            id: "{{ _entity.id }}"  # Twig expression using the entity
                        fragment: section            # Optional URL fragment

                    # Roles required for this action only (optional, overrides entity roles)
                    roles:
                        - ROLE_EDITOR

                    # Override route configuration (optional)
                    route:
                        path: /blog/new
                        parameters: {}
                        requirements:
                            id: '\d+'

                    # Override template configuration (optional)
                    view:
                        path: article/create.html.twig
                        variables:
                            # Map template variable names to alternatives accepted in the template
                            form: [articleForm, newArticleForm]

                update:
                    redirection:
                        route: article.index

                delete:
                    redirection:
                        route: dashboard
```

### Template Placeholders

Configuration values and default patterns support the following placeholders:

| Placeholder        | Example (`Article`) | Description                        |
|--------------------|---------------------|------------------------------------|
| `{{ EntityKey }}`  | `Article`           | Entity class name                  |
| `{{ EntityKeys }}` | `Articles`          | Pluralized entity class name       |
| `{{ entityKey }}`  | `article`           | Camel-cased entity key             |
| `{{ entity_key }}` | `article`           | Snake-cased entity key             |
| `{{ ActionKey }}`  | `Create`            | Action name (Pascal case)          |
| `{{ actionKey }}`  | `create`            | Action name (camel case)           |
| `{{ action_key }}` | `create`            | Action name (snake case)           |

(`dashkey`/`dashkeys` variants, e.g. `{{ entitydashkeys }}` → `articles`, provide the kebab-case forms used in URLs.)

### Overriding placeholders (`schema.keys`)

These placeholders are not hard-coded — they are themselves defined as Twig patterns under `schema.keys`, and you can override them or add your own:

```yaml
jmf_crud_engine:
    schema:
        keys:
            # Drop pluralization: route paths become "article/..." instead of "articles/...".
            entitydashkeys: "{{ entityClass|u.afterLast('\\\\').kebab }}"
            # Add a custom key, usable as "{{ EntityTitle }}" in any pattern below.
            # "App\Entity\BlogPost" -> "Blog Post"
            EntityTitle: "{{ entityClass|u.afterLast('\\\\').snake.replace({'_': ' '}).title(true) }}"
```

The contract:

- Overrides are **merged over the defaults** — declare only the keys you change.
- A key pattern may reference exactly two **source variables**: `entityClass` (the FQCN) and `action` (the action name, e.g. `create`), using Twig and the [String component `u.*` filters](https://symfony.com/doc/current/string.html#methods-to-change-the-case-of-a-string).
- A key **cannot reference another key** (keys resolve only against the source variables). Doing so fails at container build time with a clear error.
- An unknown placeholder anywhere (a typo such as `{{ entity_keyz }}`) also fails loudly at build time rather than silently rendering empty.

> The runtime placeholder `{{ _entity.id }}` used in `redirection.parameters` is **not** a key: it is resolved later, per request, against the actual entity — so it is unavailable in key/pattern definitions.

## Action Helpers

Action helpers allow you to customize behavior at specific lifecycle hooks without replacing the entire controller. Create a class implementing the appropriate interface and register it as a Symfony service.

If the class name matches a configured default pattern (e.g., `App\Controller\Article\CreateActionHelper`), it is picked up automatically. Otherwise, set `helper` explicitly in the action configuration.

> The configuration (route names/paths, form type and helper auto-discovery, view paths, etc.) is resolved once, at container build time, and cached in the compiled container. As with routes, adding a helper/form-type class that matches a discovery pattern requires a container rebuild (`cache:clear`) to be picked up.

### Create / Update Helper

```php
use Jmf\CrudEngine\Controller\Helpers\CreateActionHelperInterface;

class ArticleCreateActionHelper implements CreateActionHelperInterface
{
    /**
     * Instantiate the new entity (optional — skipping uses Doctrine Instantiator).
     */
    public function createEntity(): object
    {
        return new Article();
    }

    /**
     * Called before the entity is persisted.
     */
    public function hookBeforePersist(object $entity, array $parameters): void
    {
        // e.g. set timestamps, assign an owner
    }

    /**
     * Persist the entity (optional — skipping uses default EntityManager::persist + flush).
     */
    public function persist(object $entity): void
    {
        // custom persistence logic
    }

    /**
     * Called after the entity is persisted.
     */
    public function hookAfterPersist(object $entity, array $parameters): void
    {
        // e.g. dispatch domain events
    }

    /**
     * Extra variables passed to the template.
     */
    public function getViewVariables(object $entity, array $parameters): array
    {
        return [];
    }
}
```

### Index Helper

```php
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperInterface;

class ArticleIndexActionHelper implements IndexActionHelperInterface
{
    public function getEntities(array $parameters): iterable
    {
        // return custom entity collection
    }

    public function hookBeforeRender(iterable $entities, array $parameters): void { }

    public function getViewVariables(iterable $entities, array $parameters): array
    {
        return [];
    }
}
```

### Read Helper

```php
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperInterface;

class ArticleReadActionHelper implements ReadActionHelperInterface
{
    public function getViewVariables(object $entity, array $parameters): array
    {
        return [];
    }
}
```

### Delete Helper

```php
use Jmf\CrudEngine\Controller\Helpers\DeleteActionHelperInterface;

class ArticleDeleteActionHelper implements DeleteActionHelperInterface
{
    public function hookBeforeRemove(object $entity, array $parameters): void { }
    public function remove(object $entity): void { }
    public function hookAfterRemove(object $entity, array $parameters): void { }
    public function onFailure(object $entity, array $parameters): \Symfony\Component\HttpFoundation\Response { }
    public function getViewVariables(object $entity, array $parameters): array { return []; }
}
```

## Templates

Templates receive the entity (or collection) as a variable. The variable name is derived from the entity key (e.g., `article` for `App\Entity\Article`, `articles` for the index action).

Forms are passed as `form` (a `FormView` instance).

### Example: `templates/article/index.html.twig`

```twig
{% for article in articles %}
    <h2>{{ article.title }}</h2>
{% endfor %}
```

### Example: `templates/article/create.html.twig`

```twig
{{ form_start(form) }}
{{ form_widget(form) }}
<button type="submit">Create</button>
{{ form_end(form) }}
```

### Missing templates

When the resolved template for an action does not exist, the behavior is controlled by `schema.view.fallback`:

| Value                | Behavior                                                                 |
|----------------------|--------------------------------------------------------------------------|
| `built_in` (default) | Renders the bundle's built-in bare template (`@JmfCrudEngine/{action}.html.twig`). |
| `fail`               | Throws `CrudEngineMissingViewException`.                                  |

The built-in templates are intentionally minimal — they exist to get pages rendering immediately and to be overridden. Providing your own template at the configured path always takes precedence over the built-in one.

### Missing form types

For `create`/`update`, when no form type is configured (`form.type`) or discovered (via the `schema.form.type` patterns), the behavior is controlled by `schema.form.fallback`:

| Value               | Behavior                                                                                          |
|---------------------|---------------------------------------------------------------------------------------------------|
| `generic` (default) | Builds a generic form from the entity's Doctrine metadata (`CrudEngineEntityType`).               |
| `fail`              | Throws `CrudEngineMissingConfigurationException`.                                                  |

The generic form is a scaffold to be overridden: it maps scalar columns and `enumType` fields, and renders to-one associations as a choice of related entities; it skips identifiers, embeddables, to-many associations, and unmappable column types. Configuring or discovering a real form type always takes precedence.

To make the fallback visible wherever it renders (including under a custom template), the generated form prepends a disabled, read-only "Generated fallback form" field naming the form type class to implement to replace it.

## License

MIT
