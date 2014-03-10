# WSU / Cougar Business Network

This is the base project for CBN.  Put all found site related issues in the github issue tracker


### WSUWP Indie Development Configuration

For use under [WSUWP Indie Development](https://github.com/washingtonstateuniversity/wsuwp-indie-development)

Ensure the following is in the `sites.sls` pillar file:

```
wsuwp-indie-sites:
  cbn.wsu.edu:
    directory: cbn.wsu.edu
    database: cbn_wsu
    nginx:
      server_name: dev.cbn.wsu.edu
```
