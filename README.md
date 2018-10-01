Redis workshop
==============
This repository contains provisioning to prepare a development environment.

### Prepare development environment:
In order to set application up you must follow by steps:
1. Install Docker ([docs.docker.com/install/](https://docs.docker.com/install/)) and docker-compose ([docs.docker.com/compose/install/](https://docs.docker.com/compose/install/))
2. Build image:
 ```bash
docker-compose build
```
3. Run containers (use -d flag for “detached” mode):
```text
docker-compose up -d
```
4. Open in your browser: [localhost](localhost)