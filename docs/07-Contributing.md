# Contributing


## Creating a release

Tempo releases should be versioned with a tag and then compiled into a phar. The steps for creating a new release are:

* Update to next version number in `bin/tempo`, `README.md` and `docs/02-Installation.md`
* Commit the prepared release on master and tag it to the new version
* Run `composer install -o` to setup the vendors
* Run `bin/compile` to build the new `tempo.phar`
* Push the release to github and add the new `tempo.phar` to the release
* Set `bin/tempo`'s version back to "dev-master" and commit (leave the docs as latest stable)
