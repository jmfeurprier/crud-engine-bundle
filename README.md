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
            #   provide (default) -> build a generic form from the entity's Doctrine metadata
            #   fail              -> throw an exception
            fallback: provide

        # Default view path pattern
        view:
            path: "{{ entity_key }}/{{ action_key }}.html.twig"

            # What to do when a view template is not found:
            #   provide (default) -> render the bundle's built-in bare template
            #   fail              -> throw an exception
            fallback: provide

    entities:
        App\Entity\Aricle:
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

Examples use the entity `App\Entity\BlogPost` — a two-word name, so the casing and pluralization differences are actually visible (with a single-word entity like `Article` they'd all look the same):

| Placeholder            | Example (`BlogPost`) | Description                           |
|------------------------|----------------------|---------------------------------------|
| `{{ EntityKey }}`      | `BlogPost`           | Entity name, PascalCase               |
| `{{ EntityKeys }}`     | `BlogPosts`          | Entity name, pluralized               |
| `{{ entityKey }}`      | `blogPost`           | Entity name, camelCase                |
| `{{ entityKeys }}`     | `blogPosts`          | camelCase, pluralized                 |
| `{{ entity_key }}`     | `blog_post`          | Entity name, snake_case               |
| `{{ entity_keys }}`    | `blog_posts`         | snake_case, pluralized                |
| `{{ entitydashkey }}`  | `blog-post`          | Entity name, kebab-case               |
| `{{ entitydashkeys }}` | `blog-posts`         | kebab-case, pluralized (used in URLs) |
| `{{ ActionKey }}`      | `Create`             | Action name, PascalCase               |
| `{{ actionKey }}`      | `create`             | Action name, camelCase                |
| `{{ action_key }}`     | `create`             | Action name, snake_case               |

(Actions are always single words — `index`, `read`, `create`, `update`, `delete` — so their case variants only differ in capitalization; the `actionKeys`/`actiondashkey`/… variants exist for symmetry with the entity keys.)

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

Action helpers allow you to customize behavior at specific lifecycle hooks without replacing the entire controller. Create a class extending the matching `*ActionHelperBase` and register it as a Symfony service. Each base implements the full interface with sensible defaults (the same behavior the bundle uses when no helper is configured), so you override only the hooks you actually need.

If the class name matches a configured default pattern (e.g., `App\Controller\Article\CreateActionHelper`), it is picked up automatically. Otherwise, set `helper` explicitly in the action configuration.

> The configuration (route names/paths, form type and helper auto-discovery, view paths, etc.) is resolved once, at container build time, and cached in the compiled container. As with routes, adding a helper/form-type class that matches a discovery pattern requires a container rebuild (`cache:clear`) to be picked up.

### Create Helper

```php
use Jmf\CrudEngine\Controller\Helpers\CreateActionHelperBase;
use Override;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends CreateActionHelperBase<Article>
 */
class ArticleCreateActionHelper extends CreateActionHelperBase
{
    /**
     * Instantiate the new entity. The base does `new $entityClass()`; override only if the
     * entity needs constructor arguments. (The zero-config default uses the Doctrine Instantiator.)
     */
    #[Override]
    public function createEntity(Request $request, string $entityClass): object
    {
        return new Article();
    }

    /**
     * Called before the entity is persisted.
     */
    #[Override]
    public function hookBeforePersist(Request $request, object $entity, FormInterface $form): void
    {
        // e.g. set timestamps, assign an owner
    }

    /**
     * Extra variables passed to the template.
     */
    #[Override]
    public function getViewVariables(Request $request, object $entity): array
    {
        return [];
    }
}
```

The base also provides `persist()` (Doctrine `persist` + `flush`) and `hookAfterPersist()` — override them only to change persistence or run post-save side effects.

### Update Helper

```php
use Jmf\CrudEngine\Controller\Helpers\UpdateActionHelperBase;
use Override;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends UpdateActionHelperBase<Article>
 */
class ArticleUpdateActionHelper extends UpdateActionHelperBase
{
    /**
     * Called before the updated entity is persisted.
     */
    #[Override]
    public function hookBeforePersist(Request $request, object $entity, FormInterface $form): void
    {
        // e.g. bump an "updated at" timestamp
    }

    /**
     * Extra variables passed to the template.
     */
    #[Override]
    public function getViewVariables(Request $request, object $entity): array
    {
        return [];
    }
}
```

Same hooks as the create helper, minus `createEntity` (the entity already exists). The default `persist()` here only `flush`es the already-managed entity; `hookAfterPersist()` is also available for post-save side effects.

### Index Helper

```php
use Doctrine\Persistence\ObjectManager;
use Jmf\CrudEngine\Controller\Helpers\IndexActionHelperBase;
use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends IndexActionHelperBase<Article>
 */
class ArticleIndexActionHelper extends IndexActionHelperBase
{
    /**
     * Return a custom collection. The base does `getRepository($entityClass)->findAll()`.
     */
    #[Override]
    public function getEntities(Request $request, string $entityClass, ObjectManager $objectManager): iterable
    {
        return $objectManager->getRepository($entityClass)->findBy(['published' => true]);
    }

    #[Override]
    public function getViewVariables(Request $request): array
    {
        return [];
    }
}
```

### Read Helper

```php
use Jmf\CrudEngine\Controller\Helpers\ReadActionHelperBase;
use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ReadActionHelperBase<Article>
 */
class ArticleReadActionHelper extends ReadActionHelperBase
{
    #[Override]
    public function getViewVariables(Request $request, object $entity): array
    {
        return [];
    }
}
```

### Delete Helper

```php
use Jmf\CrudEngine\Controller\Helpers\DeleteActionHelperBase;
use Override;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends DeleteActionHelperBase<Article>
 */
class ArticleDeleteActionHelper extends DeleteActionHelperBase
{
    #[Override]
    public function hookBeforeRemove(object $entity): void
    {
        // e.g. guard against deleting a referenced entity
    }

    #[Override]
    public function getViewVariables(Request $request, object $entity): array
    {
        return [];
    }
}
```

The base also provides `remove()` (Doctrine `remove` + `flush`), `hookAfterRemove()`, and `onFailure()` (re-throws the failure) — override them as needed.

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

| Value               | Behavior                                                                           |
|---------------------|------------------------------------------------------------------------------------|
| `provide` (default) | Renders the bundle's built-in bare template (`@JmfCrudEngine/{action}.html.twig`). |
| `fail`              | Throws `CrudEngineMissingViewException`.                                           |

The built-in templates are intentionally minimal — they exist to get pages rendering immediately and to be overridden. Providing your own template at the configured path always takes precedence over the built-in one.

### Missing form types

For `create`/`update`, when no form type is configured (`form.type`) or discovered (via the `schema.form.type` patterns), the behavior is controlled by `schema.form.fallback`:

| Value               | Behavior                                                                            |
|---------------------|-------------------------------------------------------------------------------------|
| `provide` (default) | Builds a generic form from the entity's Doctrine metadata (`CrudEngineEntityType`). |
| `fail`              | Throws `CrudEngineMissingConfigurationException`.                                   |

The generic form is a scaffold to be overridden: it maps scalar columns and `enumType` fields, and renders to-one associations as a choice of related entities; it skips identifiers, embeddables, to-many associations, and unmappable column types. Configuring or discovering a real form type always takes precedence.

To make the fallback visible wherever it renders (including under a custom template), the generated form prepends a disabled, read-only "Generated fallback form" field naming the form type class to implement to replace it.

## License

MIT
