# To Do
- Add tests for individual provider implementations
- Probably extract providers into their own packages
- Add documentation for creating your own providers
- Delete created user and OAuth identity if anything goes wrong that would leave the user in an "unfinished" state after initial creation
- Add exception handling for "user creation failed" (unique constraints or just database errors, whatever)
- Maybe stop storing access token? Don't actually ever use it again, it's totally single use...
- Remove hard dependency on Session\Store, replace with some sort of "CrossRequestPersistanceInterface" or something
- Look for more opportunities to add abstractions to different provider implementations. Had to do some crappy stuff with the LinkedIn provider.
- Twitter support, going to be interesting...
