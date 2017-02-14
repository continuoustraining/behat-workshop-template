Continuous SA Behat Workshop
============================

Installation
------------

### Vagrant

```
vagrant up
```

When the machine comes up, you can ssh to it with the standard ssh forward agent:

```
vagrant ssh
```

The web root is inside the shared directory, which is at `/var/www`. Once you've ssh'd into the box, you need to cd:

```
cd /var/www
```

To install the dependencies, run:

```
./composer.phar install
```

To configure the application, run:

```
vendor/bin/phing setup-db reset-db db-migration -Ddb.name=ecommerce -Ddb.username=root -Ddb.password=secret
```