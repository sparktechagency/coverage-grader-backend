<h1 align="center">🚀 Boilerplate Backend (Laravel 12 + Stripe + Real‑time Chat + Reverb + Docker)</h1>

Production‑ready Laravel backend starter including: Authentication, Roles & Permissions, Activity Log, Real‑time Chat, Stripe One‑Time & Subscription Billing, Refund Flow, Payment Method Management, Queues & Scheduler, Redis Cache, PDF Invoices, Firebase FCM support, and a clean Service / Trait architecture.

---
## ✨ Feature Overview
- User Authentication (Register, Login, Logout, Email Verification, Password Reset via OTP)
- User Profile (View & Update)
- Role & Permission system (spatie/laravel-permission)
- Activity Logging (spatie/laravel-activitylog)
- Real‑time Chat (Conversations, Messages, Groups, Typing Indicator, Read Receipts) via Laravel Reverb
- Stripe Integration:
	- One‑Time Payments (Checkout Session + Payment Intent)
	- Subscriptions (Create / Show / Swap / Cancel / Resume)
	- Billing Portal Redirect
	- Refund handling (webhook driven)
	- Payment Methods (List, Add via SetupIntent / Setup Session, Set Default, Delete, Bulk Delete)
	- PDF Invoice Download (dompdf)
- Webhook Listener (StripeEventListener) for subscription + payment sync
- Queues (queue:work) & Scheduler (schedule:run in its own container)
- Redis (cache + queue + broadcasting readiness)
- Service Layer (BaseService + Traits pattern)
- File Upload (Image resize + optional WebP conversion)
- Tag-based caching per model (Cacheable Trait)
- Transactional create/update abstraction (ManagesData Trait)
- Fully containerized (PHP-FPM, App Nginx, Reverse Proxy, MySQL, Redis, Queue Worker, Scheduler, Reverb, phpMyAdmin)

---
## 🧱 Architecture Summary
Layered flow:
Routes → Controllers → Services → Models

Reusable Concerns (Traits): FileUploadTrait, Cacheable, ManagesData

Events & Listeners: Chat domain events + StripeEventListener for payment lifecycle

Payments: Laravel Cashier (Stripe) + custom listener to maintain local Transaction & Subscription state

Storage: MySQL (primary), Redis (caching / queues / potential broadcasting)

Realtime: Reverb WebSocket server (separate container `reverb`)

---
## 🧩 Key Packages
| Package | Purpose |
|---------|---------|
| laravel/framework 12 | Core framework |
| laravel/sanctum | API token auth |
| spatie/laravel-permission | Roles & permissions |
| spatie/laravel-activitylog | Action / audit logging |
| spatie/laravel-query-builder | Powerful filtering / sorting / includes for REST APIs |
| laravel/cashier | Stripe billing (subscriptions, invoices, payment methods) |
| dompdf/dompdf | PDF invoice rendering |
| intervention/image | Image manipulation & optimization |
| kreait/laravel-firebase | Firebase FCM push notifications |
| laravel/reverb | Real‑time broadcasting / WebSockets |
| knuckleswtf/scribe (dev) | API documentation generation |

---
## ⚙️ Docker Setup
Prerequisite: Docker & Docker Compose installed.

1. Copy environment file:
```bash
cp .env.example .env
```
2. Fill required variables (see Environment section below).
3. Build & start containers:
```bash
docker compose up -d --build
```
4. (If not auto-run) run key commands inside app container:
```bash
docker compose exec boilerplate-app php artisan key:generate
docker compose exec boilerplate-app php artisan migrate --seed
```
5. Queue worker / Scheduler / Reverb are separate containers already defined.
6. App URL: http://localhost:81
7. phpMyAdmin: http://localhost:8081
8. Reverb WebSocket: Port `${REVERB_PORT:-8085}` (default 8085)

---
## 🖥️ Manual (Non-Docker) Setup (Optional)
1. Install: PHP 8.2+, MySQL 8, Redis 7
2. `composer install`
3. `cp .env.example .env` & configure
4. `php artisan key:generate`
5. `php artisan migrate --seed`
6. `npm install && npm run build` (if front assets needed)
7. Run queue: `php artisan queue:work`
8. Run Reverb: `php artisan reverb:start`

---
## 🔐 Environment Variables (Highlights)
Stripe:
- STRIPE_KEY, STRIPE_SECRET
- STRIPE_WEBHOOK_SECRET

Cashier (optional overrides):
- CASHIER_CURRENCY (default: usd)
- CASHIER_PAYMENT_NOTIFICATION

Database:
- DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, MYSQL_ROOT_PASSWORD

Firebase:
- FIREBASE_CREDENTIALS=backend/firebase-credentials.json

Realtime / Broadcasting:
- REVERB_PORT

Mail / Slack / AWS (see `config/services.php`):
- POSTMARK_TOKEN / RESEND_KEY / AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY / SLACK_BOT_USER_OAUTH_TOKEN

---
## 🗂️ Directory Highlights
`app/Services` – Business logic (extend `BaseService`).
`app/Traits` – Reusable logic (Cacheable, ManagesData, FileUploadTrait).
`app/Listeners/StripeEventListener.php` – Handles Stripe webhooks (subscriptions, payments, refunds, card setup).
`routes/api.php` – Versioned API (v1) endpoints for auth, chat, payment.
`config/cashier.php` – Stripe Cashier config.
`database/` – Migrations / seeders (SQLite file placeholder also present).

---
## 👥 Authentication Flow
1. Register: `POST /api/v1/auth/register`
2. Login: `POST /api/v1/auth/login` (returns Sanctum token)
3. Email Verify: `POST /api/v1/auth/verify`
4. Resend Verification: `POST /api/v1/auth/resend-verification`
5. Forgot Password: `POST /api/v1/auth/forgot-password`
6. Verify Reset OTP: `POST /api/v1/auth/verify-password-otp`
7. Reset With Token: `POST /api/v1/auth/reset-password-with-token`
8. Protected access: Header `Authorization: Bearer <token>`

Profile:
- `GET /api/v1/profile/me`
- `POST /api/v1/profile/update`

Logout & Password Update:
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/update-password`

---
## 💬 Chat Module API (Protected via auth:sanctum)
Base Prefix: `/api/v1/chat`

Conversations:
- GET `/conversations`
- POST `/conversations`

Messages:
- GET `/conversations/{conversation}/messages`
- POST `/messages`
- PATCH `/messages/{message}`
- DELETE `/messages/{message}`
- POST `/messages/read` (mark as read)
- POST `/conversations/{conversation}/typing` (typing indicator)

Group Management:
- POST `/groups/{conversation}/members` (add)
- DELETE `/groups/{conversation}/members` (remove)
- POST `/groups/{conversation}/leave`
- POST `/groups/{conversation}/promote`
- POST `/groups/{conversation}/demote`

Real‑time: Subscribe to Reverb channels from frontend for message & typing events.

---
## 💳 Payment & Billing API
Prefix: `/api/v1/payment`

Stripe Webhooks:
- Internal Cashier webhook: `POST /api/v1/stripe/webhook`
- Public (web.php) fallback: `POST /stripe/webhook`

One-Time Payments:
- POST `/one-time/checkout-session`
- POST `/one-time/payment-intent`

Subscriptions:
- POST `/subscriptions` (create)
- GET `/subscriptions` (show current)
- POST `/subscriptions/cancel`
- POST `/subscriptions/resume`
- POST `/subscriptions/swap`

Invoices:
- GET `/invoices`
- GET `/invoices/{invoice}/download`

Refunds:
- POST `/refunds`

Payment Methods:
- GET `/payment-methods`
- POST `/payment-methods`
- POST `/payment-methods/setup-intent`
- POST `/payment-methods/setup-session`
- PATCH `/payment-methods/{id}/set-default`
- DELETE `/payment-methods/{id}`
- DELETE `/payment-methods`

Billing Portal:
- POST `/billing-portal`

---
## 🔄 Subscription Lifecycle (Stripe + Cashier)
1. User without `stripe_id` is created automatically by Cashier on first billing action.
2. `customer.subscription.created` → Listener upserts local subscription row.
3. `customer.subscription.updated` → Syncs status, trial end, cancel period end.
4. `invoice.payment_succeeded` / `charge.succeeded` → Creates/updates Transaction (amount, receipt, period info).
5. `refund.created` → Appends refund info to Transaction metadata; updates status.
6. Checkout Session (mode=setup) → Adds payment method (fingerprint duplicate avoidance).
7. Cancel sets `ends_at` (active until period end); resume nulls it.

---
## 🧾 Transaction Model (Concept)
Webhook → Listener → `Transaction::updateOrCreate` (idempotent). Metadata stores invoice ID, charge ID, receipt URL, billing period, hosted invoice links, refund details.

---
## 🛠️ Trait Usage
### FileUploadTrait
```php
$path = $this->handleFileUpload($request, 'avatar', 'avatars', width: 600, height: 600, forceWebp: true);
```
Delete:
```php
$this->deleteFile($pathOrUrl);
```

### Cacheable
```php
return $this->cache(__FUNCTION__, func_get_args(), function () {
		return $this->model->latest()->get();
});
```

### ManagesData
```php
$entity = $this->storeOrUpdate($data, $modelInstance, ['roles' => [1,2]]);
```

### BaseService Example
```php
class UserService extends BaseService {
		protected string $modelClass = User::class;
}
```

---
## 🔍 Advanced Query Filtering (spatie/laravel-query-builder)
This project ships with the excellent `spatie/laravel-query-builder` package for expressive, secure, whitelist‑based filtering, sorting, sparse fieldsets, includes, & pagination.

### Why Use It?
Instead of manually parsing query params, you declare what is allowed. The package prevents unapproved column / relation access and gives consistent patterns for the frontend.

### Installation (already done if you're reading this)
```bash
composer require spatie/laravel-query-builder
```

Optional publish (rarely needed):
```bash
php artisan vendor:publish --provider="Spatie\\QueryBuilder\\QueryBuilderServiceProvider"
```

### Key Concepts
- `QueryBuilder::for(Model::class)` – start a query.
- `allowedFilters([...])` – whitelist filters (exact, partial, custom Filter classes).
- `allowedSorts([...])` – whitelist sortable columns (prefix `-` for desc).
- `allowedIncludes([...])` – allow relationship eager loading.
- `allowedFields([...])` – restrict selected columns (sparse fieldsets per resource type).
- `paginate()` / `simplePaginate()` – integrate with automatic `page[size]` + `page[number]` style params.

### Global Search Filter
Located at `app/Filters/GlobalSearchFilter.php`. It lets one search term run LIKE queries across multiple columns using a single filter key.

```php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class GlobalSearchFilter implements Filter
{
	public function __invoke(Builder $query, $value, string $property)
	{
		$columns = explode(',', $property); // comma separated columns passed when declaring the filter

		$query->where(function (Builder $q) use ($columns, $value) {
			foreach ($columns as $column) {
				$q->orWhere($column, 'LIKE', "%{$value}%");
			}
		});
	}
}
```

### Declaring Filters (Service or Controller Layer)
Example inside a `UserService` method (fits the existing architecture):
```php
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\User;
use App\Filters\GlobalSearchFilter;

public function list(array $extra = [])
{
	return QueryBuilder::for(User::query())
		->allowedFilters([
			// Exact filters
			AllowedFilter::exact('id'),
			AllowedFilter::exact('status'),
			// Partial (LIKE) filters (package provides partial by default for strings)
			'name', 'email',
			// Global search across multiple columns (name,email,phone)
			AllowedFilter::custom('global', new GlobalSearchFilter())->default(''), // usage: ?filter[global]=john
		])
		->allowedSorts(['id', 'name', 'created_at']) // usage: ?sort=-created_at
		->allowedIncludes(['roles', 'permissions'])   // usage: ?include=roles,permissions
		->allowedFields([
			'users.id', 'users.name', 'users.email', 'users.status'
		])
		->paginate(request('page.size', 15))
		->appends(request()->query()); // keep query params in pagination links
}
```

### Using Column List with Global Search
When you declare the filter you can pass the column list in the property name: the `property` argument delivered to the filter is what you register. Two common patterns:
1. Hardcode inside filter (current implementation using `explode(',', $property)` lets you change at declaration time).
2. Register multiple global filters if needed.

Register like:
```php
AllowedFilter::custom('search', new GlobalSearchFilter(), 'name,email,phone') // (Alt pattern if you extend the helper)
```

But with the existing signature you typically do:
```php
AllowedFilter::custom('global', new GlobalSearchFilter()) // and inside filter decide columns
```
If you want dynamic columns without editing the filter, you can extend it to read from config.

### Example API Requests
```http
GET /api/v1/users?filter[name]=john
GET /api/v1/users?filter[global]=gmail&sort=-created_at
GET /api/v1/users?filter[status]=active&include=roles,permissions
GET /api/v1/users?filter[global]=smith&fields[users]=id,name,email&sort=name
```

### Sparse Fieldsets
Specify fields per resource type (model table / JSON resource key):
`?fields[users]=id,name,email` → returns only those columns for users (plus any required keys added by transformers/resources if you wrap them).

### Security Notes
- Only whitelist columns you genuinely want exposed. The package ignores undeclared filters/sorts/includes.
- Avoid allowing arbitrary `fields` for sensitive tables (e.g., don't include password / secret tokens anyway since they should be hidden in model `$hidden`).
- Pair with authorization checks (Policies / Gates) if filtering across relationships (e.g., `company_id`).

### Performance Tips
- Index commonly filtered columns (`status`, `email`, timestamps) in migrations.
- For expensive global searches consider full‑text indexes (MySQL `FULLTEXT`) or a search engine (Scout + Meilisearch) if performance declines.

### Testing Example
```php
public function test_can_filter_users_by_global_search()
{
	User::factory()->create(['name' => 'Alice Alpha']);
	User::factory()->create(['name' => 'Bob Beta']);

	$response = $this->getJson('/api/v1/users?filter[global]=Alpha');
	$response->assertOk()->assertJsonFragment(['name' => 'Alice Alpha'])
			 ->assertJsonMissing(['name' => 'Bob Beta']);
}
```

### Frontend Integration Pattern
Build a query param builder (JS):
```ts
const qs = new URLSearchParams({ 'filter[global]': searchTerm, sort: '-created_at' });
qs.append('include','roles,permissions');
fetch(`/api/v1/users?${qs.toString()}`);
```

### Quick Cheat Sheet
| Capability | Param Example |
|------------|---------------|
| Global search | `?filter[global]=john` |
| Exact filter | `?filter[status]=active` |
| Partial filter | `?filter[name]=ali` |
| Sort desc | `?sort=-created_at` |
| Multiple sorts | `?sort=name,-id` |
| Includes | `?include=roles,permissions` |
| Sparse fieldset | `?fields[users]=id,name,email` |
| Page size | `?page[size]=50` |
| Page number | `?page[number]=2` |

---

---
## 🧪 Testing
Inside Docker:
```bash
docker compose exec boilerplate-app composer test
```
Or locally:
```bash
php artisan test
```
Generate API docs (if Scribe configured):
```bash
php artisan scribe:generate
```

---
## 🚀 Deployment Notes
- Ensure correct ENV (Stripe keys, webhook secret, queue workers, scheduler, reverb port).
- Consider Horizon / Supervisor for robust queue management.
- Configure Redis persistence for production.
- Enable HTTPS at reverse proxy (nginx proxy container ready).

---
## 🐞 Troubleshooting
| Issue | Cause | Fix |
|-------|-------|-----|
| 419 / CSRF (SPA) | Sanctum / domain mismatch | Align SESSION_DOMAIN & frontend origin |
| Webhook 400 | Wrong STRIPE_WEBHOOK_SECRET | Update `.env`; test via Stripe CLI |
| Duplicate payment method skipped | Same card fingerprint | Expected (dedup logic) |
| Subscription missing | Webhook not processed | Ensure queue worker running |
| Reverb connection fails | Port or CORS misconfig | Expose REVERB_PORT & adjust JS client |

---
## 📝 Suggested Next Steps
- Add Horizon integration
- Fine-grained feature rate limiting
- Chat search indexing (Scout/Meilisearch)
- Persist webhook signature logs
- Multi-plan & tiered pricing management UI

---
## 📜 License
MIT (inherits Laravel base license).

---
## 🙌 Contributing
Open issues / PRs with concise commits and focused changes.

---
## 📩 Contact
Create an issue for improvements to Stripe or Chat modules.

---
### ✅ Summary
Fast launch foundation for a SaaS / Real‑time app: Auth, Billing, Chat, Queues, Caching, File Uploads, PDF Invoices—all wired and extensible.

Enjoy & Ship Fast! 🚀


---
## 🔌 Frontend Integration (React & React Native)
Integrate with real-time + REST APIs using Laravel Echo (Reverb / Pusher protocol) and Axios.

### Backend Broadcast Essentials
`.env` keys:
```
BROADCAST_DRIVER=reverb
REVERB_APP_KEY=local-app-key
REVERB_APP_SECRET=app-secret
REVERB_APP_ID=app-id
REVERB_HOST=0.0.0.0
REVERB_PORT=8085
REVERB_SCHEME=http
```
Private / presence auth: `/broadcasting/auth` with Bearer token.

Event example:
```php
class MessageSent implements ShouldBroadcast {
	public function __construct(public Message $message) {}
	public function broadcastOn(): array { return [new PrivateChannel('conversations.' . $this->message->conversation_id)]; }
	public function broadcastWith(): array { return [ 'id'=>$this->message->id,'body'=>$this->message->body,'user_id'=>$this->message->user_id,'conversation_id'=>$this->message->conversation_id,'created_at'=>$this->message->created_at->toISOString(), ]; }
}
```

### React Web
`.env.local`:
```
VITE_API_URL=http://localhost:81/api/v1
VITE_AUTH_TOKEN_KEY=auth_token
VITE_REVERB_APP_KEY=local-app-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8085
VITE_REVERB_SCHEME=http
```
Install:
```bash
npm i axios laravel-echo pusher-js
```
`src/lib/api.ts`:
```ts
import axios from 'axios';
const api = axios.create({ baseURL: import.meta.env.VITE_API_URL });
api.interceptors.request.use(c=>{const t=localStorage.getItem(import.meta.env.VITE_AUTH_TOKEN_KEY || 'token'); if(t)c.headers.Authorization=`Bearer ${t}`; c.headers.Accept='application/json'; return c;});
export default api;
```
`src/lib/echo.ts`:
```ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
(window as any).Pusher=Pusher;
const authHeaders=()=>{const t=localStorage.getItem(import.meta.env.VITE_AUTH_TOKEN_KEY||'token');return t?{Authorization:`Bearer ${t}`}:{}};
export const echo=new Echo({broadcaster:'reverb',key:import.meta.env.VITE_REVERB_APP_KEY,wsHost:import.meta.env.VITE_REVERB_HOST,wsPort:+(import.meta.env.VITE_REVERB_PORT||8085),wssPort:+(import.meta.env.VITE_REVERB_PORT||8085),forceTLS:import.meta.env.VITE_REVERB_SCHEME==='https',enabledTransports:['ws','wss'],authEndpoint:'/broadcasting/auth',auth:{headers:authHeaders()}});
```
Component usage:
```tsx
useEffect(()=>{const ch=echo.private(`conversations.${conversationId}`);ch.listen('MessageSent',(e:any)=>setMessages(m=>[...m,e]));ch.listenForWhisper('typing',(w:any)=>setTypingUser(w.user_id));return ()=>echo.leave(`private-conversations.${conversationId}`);},[conversationId]);
```

### React Native
`.env`:
```
API_URL=http://10.0.2.2:81/api/v1
REVERB_APP_KEY=local-app-key
REVERB_HOST=10.0.2.2
REVERB_PORT=8085
```
Install:
```bash
npm i axios laravel-echo pusher-js @react-native-async-storage/async-storage
```
`echo.ts`:
```ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js/react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
export const echo=new Echo({broadcaster:'reverb',key:process.env.REVERB_APP_KEY,wsHost:process.env.REVERB_HOST,wsPort:Number(process.env.REVERB_PORT||8085),forceTLS:false,enabledTransports:['ws','wss'],authEndpoint:'http://10.0.2.2:81/broadcasting/auth',authorizer:(channel,options)=>({authorize:async(socketId,cb)=>{try{const token=await AsyncStorage.getItem('auth_token');const res=await fetch(options.authEndpoint,{method:'POST',headers:{'Content-Type':'application/json','Authorization':token?`Bearer ${token}`:'','X-Socket-ID':socketId},body:JSON.stringify({channel_name:channel.name})});if(!res.ok)throw new Error('Auth failed');cb(false,await res.json());}catch(e){cb(true,e);}}})});
```
`api.ts`:
```ts
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
export const api=axios.create({baseURL:process.env.API_URL});
api.interceptors.request.use(async c=>{const t=await AsyncStorage.getItem('auth_token'); if(t)c.headers.Authorization=`Bearer ${t}`; c.headers.Accept='application/json'; return c;});
```

### Channel Authorization (`routes/channels.php`)
```php
Broadcast::channel('conversations.{id}', fn($user,$id)=>$user->canAccessConversation($id));
Broadcast::channel('presence.conversations.{id}', function($user,$id){ if($user->canAccessConversation($id)){ return ['id'=>$user->id,'name'=>$user->name]; } return false;});
```

### Typing Whisper
```ts
echo.private(`conversations.${conversationId}`).whisper('typing',{user_id:currentUserId,typing:true});
```

### Quick Test
1. Login & store token.
2. Console: `window.Echo.private('conversations.1').listen('MessageSent',console.log)`.
3. POST `/api/v1/chat/messages` → event appears.
4. Whisper typing → second client sees indicator.

### Common Issues
| Problem | Cause | Fix |
|---------|-------|-----|
| 403 /broadcasting/auth | Missing token | Add Authorization header |
| No events | Channel typo | Match exact channel | 
| Whispers missing | Used listen() | Use listenForWhisper() |
| RN cannot connect | Using localhost | Use 10.0.2.2 / device IP |
| Presence empty | Returned boolean only | Return user data array |

