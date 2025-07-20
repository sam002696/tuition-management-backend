# React + Laravel Echo Integration Example

## `src/App.jsx`

```jsx
// src/App.jsx
import { useEffect, useState } from "react";
import echo from "./echo";

function App() {
    const [notification, setNotification] = useState(null);

    useEffect(() => {
        const userId = 1; // Replace with your authenticated user's ID

        echo.private(`App.Models.User.${userId}`).notification(
            (notification) => {
                console.log(" New Notification:", notification);
                setNotification(notification);
            }
        );

        return () => {
            echo.leave(`App.Models.User.${userId}`);
        };
    }, []);

    return (
        <>
            <h1>Vite + React + Laravel Echo</h1>
            <div className="card">
                {notification ? (
                    <div>
                        <h3>New Notification</h3>
                        <p>{notification.title}</p>
                        <p>{notification.body}</p>
                    </div>
                ) : (
                    <p>No new notifications</p>
                )}
            </div>
        </>
    );
}

export default App;
```

## `src/echo.js`

```js
// src/echo.js
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: "pusher",
    key: "74ea762aea46c2f81ccf",
    cluster: "ap2",
    forceTLS: true,
    encrypted: true,
    authEndpoint: "http://localhost:8000/broadcasting/auth",
    auth: {
        headers: {
            Authorization: `Bearer 1|1aRuOtUPGFEZG3gVMBdwW6jaGvpg2SNmDDHfqJJ4975a7ad9
`, // If you're using bearer tokens
        },
    },
});

export default echo;
```
