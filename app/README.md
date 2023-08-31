# Frontend Deploy

```
cd app
cp .env.example .env
```

## Build docker container
```
make build
```

### Start docker container
```
make start
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

### Available host
See [http://127.0.0.1:8081](http://127.0.0.1:8081)

