# Apple In-App Purchases

Because it's impossible to use payment providers such as Stripe to handle digital subscriptions in the app, and due to the complex Apple documentation regarding In-App purchases,
I decided to publish the working code to handle purchases using Symfony and PHP.

This code contains a simple and stable example of the Apple In-App purchases implementation using Symfony.

It means that it correctly validates the Apple Receipt and subscribes to Apple Notifications.

To decode/encode Apple keys, the `firebase/php-jwt` with OpenSSL PHP extension is used.
See `src/Apple/Token/Decoder.php` and `src/Apple/Token/Encoder.php`. No other non-Symfony initial vendors are used.

It depends on the `symfony/messenger` to handle any possible fail cases in the runtime.

To set up the project, you need to configure the Apple ENV parameters.
You can do it using 
- https://developer.apple.com/help/account/configure-app-capabilities/enabling-server-to-server-notifications/
- https://developer.apple.com/documentation/storekit/in-app_purchase/original_api_for_in-app_purchase/subscriptions_and_offers/enabling_app_store_server_notifications
- https://developer.apple.com/documentation/appstoreserverapi/creating_api_keys_to_authorize_api_requests
- https://developer.apple.com/documentation/appstoreserverapi/generating_json_web_tokens_for_api_requests


```
APPLE_DEBUG=true
#shared secret
APPLE_SHARED_SECRET=!ChangeMe!
#bunble name
APPLE_BUNDLE_ID=com.app
#issuer id
APPLE_API_ISSUER_ID=!ChangeMe!
#private key to communicate
APPLE_API_PRIVATE_KEY_ID=!ChangeMe!
```

The example contains only the set of files. The example itself could not work directly after cloning the repository. 

You can reuse the code if you want. If you have any suggestions you can create a pull request to discuss it.
