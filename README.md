twitterAppAuth
==============

PHP class for Twitter's Application-only authentication model
--------------

Twitter offers applications the ability to issue authenticated requests on behalf of the application itself (as opposed to on behalf of a specific user).
As Twitter API 1.1 requires almost all calls to be authenticated getting info from Twitter for your app you need some way to authenticate requests. This class implements this behaviour. This class does not authenticate a user. As Twitter says:

When issuing requests using application-only auth, there is no concept of a "current user." Therefore, endpoints such as POST statuses/update will not function with application-only auth. See [using OAuth](https://dev.twitter.com/docs/auth/using-oauth) for more information for issuing requests on behalf of a user.

More info can be found at [https://dev.twitter.com/docs/auth/application-only-auth](https://dev.twitter.com/docs/auth/application-only-auth)

Currently only API Call function is ``getUserInfo($username)``. This method is gets a user's information from twitter and acts as an example method.

```
// If you run the class first time
// supply your credentials
// Example:
$twitter = new twitterAppAuth('YOUR_KEY', 'YOUR_SECRET');

$user = $twitter->getUserInfo('sinantaga');
```

or, if you use dependecy injection, like in Symfony2:

```
twitter_auth:
    class: TwitterAppAuth\Auth
    arguments:
        - %twitter_consumer_key%
        - %twitter_consumer_secret%
```
