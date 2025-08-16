# Frontend Deploy - Build docker container
```
cd app
make generate-env
make build
```

### Start docker container
```
make start
```

## Test User credentials
```
test0@example.com
password
```

### Stop docker container
```
make stop
```

### Start development and fixes
```
make ssh
npm run serve
```

