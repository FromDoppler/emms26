FROM nginx:1.18.0

LABEL Maintainer = "Matias Cappato <mcappato@fromdoppler.com>"

COPY ./default.conf /etc/nginx/conf.d/default.conf

COPY ./nginx.conf /etc/nginx/nginx.conf

ARG WEB_PORT

RUN export WEB_PORT=${WEB_PORT} ; sed -i -r "s/WEB_PORT/${WEB_PORT}/g" /etc/nginx/conf.d/default.conf
