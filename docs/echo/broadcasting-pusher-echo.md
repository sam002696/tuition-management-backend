# ✅ Broadcasting: Connection Request Notification Flow – Task Breakdown

## 🔧 1. Setup Laravel Broadcasting with Pusher

-   Configured broadcasting in `.env`:

    ```env
    BROADCAST_DRIVER=pusher
    PUSHER_APP_ID=xxx
    PUSHER_APP_KEY=xxx
    PUSHER_APP_SECRET=xxx
    PUSHER_APP_CLUSTER=mt1
    ```

-   Updated `config/broadcasting.php`:

    -   Set default broadcaster to `pusher`.
    -   Verified pusher connection settings with `useTLS: true`.

-   Registered broadcasting routes with sanctum in `BroadcastServiceProvider.php`:

    ```php
    Broadcast::routes(['middleware' => ['auth:sanctum']]);
    ```

-   Created a private channel in `routes/channels.php`:
    ```php
    Broadcast::channel('App.Models.User.{receiverId}', function ($user, $receiverId) {
        return (int) $user->id === (int) $receiverId;
    });
    ```

## 📬 2. Created `ConnectionRequestNotification`

-   Created a notification class to support:
    -   **Database** notifications (`toDatabase`)
    -   **Broadcast** (real-time) notifications (`toBroadcast`)
-   Included necessary fields: `title`, `body`, `request_id`, `type`, `audience_role`.

## 🧠 3. Built the Core Logic in `ConnectionRequestService`

-   **sendRequest(Request $request):**

    -   Validates request.
    -   Ensures only **teachers** can send.
    -   Checks:
        -   No existing accepted request.
        -   No existing pending request.
    -   Creates new connection request.
    -   Notifies student using `ConnectionRequestNotification`.

-   **respondToRequest(Request $request, $id):**

    -   Validates request.
    -   Ensures only **students** can respond.
    -   Updates connection request status.
    -   Notifies teacher using `ConnectionRequestNotification`.

-   **getUserRequests($user):**
    -   Returns requests based on user role (`teacher` or `student`).

## ⚙️ 4. Exception Handling with `abort()`

-   Replaced custom `ApiResponseService::errorResponse(...)` calls with clean:
    ```php
    abort(403, 'Only teachers can send requests.');
    ```
-   Ensures Laravel’s error responses (JSON) stay consistent and easy to debug.

## 🖥️ 5. Frontend (Vite + Laravel Echo + Pusher)

-   Used `laravel-echo` and `pusher-js` to listen to private channels.
-   Subscribed to:

    ```js
    echo.private(`App.Models.User`);
    ```

    instead of `App.Models.User.${userId}` due to your notification being broadcasted on `App.Models.User`.

-   Successfully received real-time notifications in the frontend and logged them.

## 🧪 6. Tested End-to-End

-   Confirmed:
    -   Connection requests send successfully.
    -   Rejected/accepted status updates work.
    -   Realtime notifications reach the correct user.
    -   Channel authorization via `auth:sanctum` works.
    -   Duplicate or invalid requests are handled gracefully with HTTP error responses.

## 📄 Summary

You implemented a **full real-time notification system** for connection requests using:

-   Laravel Broadcasting with Pusher
-   Notification system with `database` + `broadcast` channels
-   Role-based access control (`teacher` / `student`)
-   Dynamic private channels (`App.Models.User.{id}`)
-   Frontend listener using Laravel Echo
-   Clean error handling and user feedback
