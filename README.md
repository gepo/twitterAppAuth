twitterAppAuth
==============

PHP class for Twitter's Application-only authentication model
--------------

Twitter offers applications the ability to issue authenticated requests on behalf of the application itself (as opposed to on behalf of a specific user).
As Twitter API 1.1 requires almost all calls to be authenticated getting info from Twitter for your app you need some way to authenticate requests. This class implements this behaviour. 

More Info can be found at [https://dev.twitter.com/docs/auth/application-only-auth](https://dev.twitter.com/docs/auth/application-only-auth)

```
// Example:
$twitter = new twitterAppAuth();
$user = $twitter->getUserInfo('sinantaga');
```