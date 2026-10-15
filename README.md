# Manifay — invoice management module

Full-stack test assignment: Nuxt 4 + Laravel 12, a minimal but realistic module for working with invoices (list, details, editing).

## Stack

- **Frontend:** Nuxt 4 (`ssr: false`), Vue 3.5, TailwindCSS 4, vee-validate + zod
- **Backend:** PHP 8.3, Laravel 12
- **DB:** MySQL 8.0
- **Docker:** docker-compose (backend + frontend + db)

## Running it

```bash
docker compose up
```

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api
- OpenAPI spec (`POST /api/invoices`): http://localhost:8000/api/docs (Swagger UI; raw JSON — http://localhost:8000/api/openapi.json)
- On first start the `backend` service runs `composer install`, migrations and seeding itself (demo data, including approved/rejected invoices).

## Code quality

```bash
docker compose exec backend composer lint      # Pint — formatting
docker compose exec backend composer analyse   # Larastan — static analysis
docker compose exec backend php artisan test    # PHPUnit — Feature tests
docker compose exec frontend npm run lint       # ESLint
docker compose exec frontend npm run typecheck  # vue-tsc
```

No frontend tests (not even a test runner is installed) — see "What would you improve for a production version?".

**Git hooks** (`.githooks/`): pre-commit — Pint + ESLint (fast), pre-push — Larastan + typecheck (slower). After cloning the repo, run once:
```bash
git config core.hooksPath .githooks
```
(`core.hooksPath` is a local setting, not carried over automatically on clone; the hooks require `docker compose up` to be running, since they execute via `exec` into already-live containers.)

## API

| Method | Path | Description |
|---|---|---|
| GET | `/api/invoices` | invoice list — pagination, sorting (`?sort=created_at\|due_date&direction=asc\|desc&page=`) |
| GET | `/api/invoices/{id}` | a single invoice |
| POST | `/api/invoices` | creation (no UI — see trade-offs below) |
| PUT | `/api/invoices/{id}` | update (only for `status = pending`) |

`POST /api/invoices` is additionally documented in OpenAPI (`backend/app/OpenApi/OpenApiSpec.php` + attributes on `InvoiceController::store()`/`InvoiceResource`) — this is precisely the endpoint with no UI, so the formal spec gives it a contract independent of the frontend. Swagger UI: http://localhost:8000/api/docs. Regenerate after changing the attributes: `docker compose exec backend composer docs` → `backend/storage/api-docs/openapi.json`.

## Answers to the assignment questions

### 1. How did you structure the frontend and backend?

**Backend:** `Controller (thin) → UseCase → Repository`, without Ports/interfaces and without a separate Domain layer — the Eloquent model is both the persistence and the data carrier at once. A `UseCase` exists only where there's actual business logic (`CreateInvoiceUseCase`, `UpdateInvoiceUseCase` — computing `gross_amount`, guarding on `status`); for plain reads (`index`/`show`) the controller talks directly to the `Repository`, without an empty delegate.

**Frontend:** the minimalist Nuxt 4 convention (`app/pages`, `app/components`, `app/composables`, `app/schemas`, `app/types`), with no custom feature-folder grouping of its own — one small module doesn't justify additional structure beyond what the Nuxt convention already gives.

### 2. What trade-offs did you make, and why?

- **No create form on the frontend.** Sections 5-6 of the assignment describe only the list/details/editing — there's no creation form there, even though `POST /api/invoices` is required by the backend. Implemented `POST` in full (with validation); demo data is populated via `DatabaseSeeder`. Since the endpoint has no UI consumer (and therefore "hangs in the air" without a formal contract), it's additionally documented in OpenAPI — see the [API](#api) section.
- **No status-change mechanism.** The assignment never describes a `pending → approved/rejected` transition. Instead of a separate API — `InvoiceSeeder` directly creates some invoices with `approved`/`rejected` statuses, to demonstrate the locked edit form on real data.
- **`gross_amount` isn't accepted as an input field.** The assignment phrases `gross_amount = net_amount + vat_amount` as a validation rule (as if the client sends the value and the server checks it). Instead, the backend computes `gross_amount` itself and ignores any client-supplied value — stricter than the literal reading (an inconsistent number can't be sent at all), the same invariant is enforced.
- **No Ports/interfaces between `UseCase` and `Repository`.** Deliberate — one backend, one DB, no need to swap storage; less boilerplate for a 4-6 hour budget. Downside: `UseCase` is tightly coupled to a concrete Eloquent class, making a unit test without a real DB harder (you'd have to mock the concrete class, not an interface).
- **CORS instead of a Nuxt server proxy.** Simpler for a two-container docker-compose, doesn't add an intermediate layer for a task where there's nothing to hide behind one.
- **`ssr: false` in Nuxt.** An internal CRUD admin screen with no need for SEO/SSR — Nuxt stays in the stack (an assignment requirement), but without the SSR complexity that isn't needed here.

### 3. What would you improve for a production version?

- Ports/interfaces for `Repository`, in case storage needed to be swapped or `UseCase` needed clean unit testing without a real DB.
- An explicit status-change endpoint (`PATCH /api/invoices/{id}/status`) with its own transition validation (e.g. forbidding `approved → pending`).
- Authentication and authorization (explicitly out of scope per the assignment, but mandatory in production for financial data).
- Filtering by status/supplier in `GET /api/invoices` (currently there's pagination and sorting by `created_at`/`due_date`, but no filters).
- Idempotency of `POST /api/invoices`. Currently only protected by a DB row uniqueness constraint (`unique:invoices,number`) — a repeated POST with the same `number` gets a `422`, no duplicate is created. But that's not idempotency: on retry (e.g. after a lost connection) the client sees an error rather than the ID of the already-created invoice, and can't tell "this is my own retry" apart from "someone else entered the same number". The correct solution is an `Idempotency-Key` header (as in Stripe): the client generates a key per submission attempt, and the server returns the same successful result for that key again rather than reprocessing the request.
- Frontend tests — currently no test runner is installed at all (Vitest/@vue/test-utils). The backend is covered by Feature tests (`php artisan test`), the frontend isn't.
- A warning about unsaved changes when leaving the page — currently form data is silently lost on navigating away or closing without submitting.
- Search/filter in the list (by number, supplier) — currently only sorting and pagination; with hundreds of invoices, search becomes genuinely necessary.
- Centralized network-error notifications — each page currently draws its own inline error block, there's no single pattern.
- A progress indicator between page transitions (list → details) — there's no instant visual feedback between clicking a row and the skeleton appearing.
- Design tokens/a component library instead of ad-hoc Tailwind classes on every element — the current approach doesn't scale well beyond one small module.
- Localization (i18n) — all text is currently hardcoded in Ukrainian.
- Creating an invoice via a queue — currently `CreateInvoiceUseCase` runs synchronously right inside the HTTP request. For production it's worth moving it to a `CreateInvoiceJob` via a Redis queue, so heavy operations (a future accounting-system integration, notifications) don't block the client's response.
- Redis for cache and queues — currently `QUEUE_CONNECTION=sync` (the queue runs synchronously, effectively there is none) and `CACHE_STORE=array` (in-memory, non-persistent, not shared between processes/workers). Redis would give a real persistent cache (e.g. caching the invoice list) and a working async queue.
- DB schema — `supplier_name`/`supplier_tax_id` are currently duplicated on every `invoices` row instead of a separate `suppliers` table (normalization, avoiding supplier-data desync between invoices). Also no indexes on `due_date`/`status` (sorting/filtering will full-table-scan as volume grows) and no audit fields (`created_by`/`updated_by`) — important for financial data.

### 4. What UX edge cases did you account for?

- Empty invoice list — a distinct state, not an empty table.
- Error loading the list/details — a message + retry, not an infinite spinner.
- Loading — skeleton placeholders (table/card shape), not just a "Loading…" text.
- Invalid/nonexistent `id` in the URL — an explicit "not found", instead of infinite loading.
- Returning from the invoice details page to the list doesn't reset the page or sorting — "← Back to list" works via `router.back()`, identical to the browser's native back button.
- Locked edit form for `status != pending` — the "Save" button is fully hidden (not just `disabled`), with an explanation of why.
- Race between loading the form and submitting — separate handling for 409 (invoice is no longer `pending`) and 422 (server validation failed), plus a network error during submit (a separate branch asking to check the connection).
- Rounding during live client-side recalculation of `gross_amount` (avoiding float errors like 0.1+0.2).
- A spinner + "Saving…" on the button during form submit, a green "✓ Saved" confirmation next to the invoice number on success — the button stays disabled until it disappears (2s), to prevent a duplicate submit.
- Long values in list columns (`supplier_name`, `number`) — truncated with a `title` tooltip.
- Responsiveness — on mobile the table shows only Number+Status (the rest is revealed inline by tapping, without navigating to another page), sorting by due date is available via a separate button.
- Accessibility — `label`/`input` linked via `for`/`id`, `aria-invalid`/`aria-describedby` on validation errors, full keyboard navigation of table rows.
