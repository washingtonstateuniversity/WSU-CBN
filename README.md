# WSU / Cougar Business Network

This is the base project for CBN.  Put all found site related issues in the github issue tracker


### WSUWP Single Site Dev Configuration

For use under [WSUWP Single Site Dev](https://github.com/washingtonstateuniversity/WSUWP-Single-Site-Dev)

ensure the following is in the `projects.sls` pillar file:

```
wp-single-projects:
  cbn.wsu.edu:
    name: cbn.wsu.edu
    database: wsu_cbn
```
