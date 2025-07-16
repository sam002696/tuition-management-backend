# 🧾 Sanctum Error: Missing `personal_access_tokens` Table

## ❗ Error Message

```json
{
  "data": null,
  "status": "error",
  "message": "Something went wrong",
  "errors": "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'tuition_db.personal_access_tokens' doesn't exist (Connection: mysql, SQL: insert into `personal_access_tokens` (...))"
}
```

---

## 🧠 Cause

This error occurs when:
- Laravel Sanctum is installed
- But the required `personal_access_tokens` table has **not been migrated**

---

## 🛠️ Solution

### ✅ Step 1: Publish Sanctum Migrations

If the migration doesn’t exist in `database/migrations`, run:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

> This will generate:
> ```
> database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php
> ```

---

### ✅ Step 2: Run Migrations

```bash
php artisan migrate
```

> This will create the missing table and resolve the issue.

---

## 🧪 How to Verify

Run this SQL in your DB:
```sql
SHOW TABLES LIKE 'personal_access_tokens';
```

You should see it listed in the output.

---

## ✅ Final Setup Checklist

- [x] `composer require laravel/sanctum`
- [x] Published Sanctum migrations
- [x] Ran `php artisan migrate`
- [x] `HasApiTokens` added to `User` model
- [x] Routes protected with `auth:sanctum`
- [x] Token generated using `$user->createToken()`

---

## 📌 Notes

If you're using `php artisan migrate:refresh`, this table is also included. You may need to reseed data afterwards.

---
