# Replicating Memcache Inconsistency Issue

tl;dr - the wrong value can be returned for a key if the key is requested while the php request ooms and the next web request asks for a different key.

1) Run `docker compose up` to build up the environment.
2) Visit `http://localhost:8082/index.php?set_keys` to hydrate the keys
3) Run `curl -s  http://localhost:8082/index.php` a few times, or just visit the page in browser a bunch.
4) After a while, switch to `http://localhost:8082/index.php?no_oom` and load a few times.

During step 3 watch the php logs (logs/php/error.log) and you'll likely see the issue occurring (the wrong value for the canary key being returned). On step 4 you'll see there is a backlog of error-some returns, but eventually it returns to consistency.

Alternatively, run `./page-requester` and just look at the PHP logs :).
