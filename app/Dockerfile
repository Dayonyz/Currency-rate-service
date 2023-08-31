FROM node:lts-alpine

RUN npm install -g http-server
RUN apk update && apk add rsync

WORKDIR /usr/local/tmp

COPY package*.json ./

RUN npm install && npm cache clean --force \
    && npm install -g @vue/cli

#    && cp -r node_modules /node_modules
#ENV PATH=/usr/local/node_modules/.bin:$PATH
WORKDIR /usr/local/app
RUN cp -r /usr/local/tmp/node_modules /usr/local/app/node_modules
RUN cp /usr/local/tmp/package*.json /usr/local/app
#RUN rsync -avu --delete "/node_modules/" "/app/node_modules"

COPY . .

RUN npm run build

EXPOSE 8080
CMD [ "http-server", "dist" ]
CMD ["npm", "run", "serve"]
