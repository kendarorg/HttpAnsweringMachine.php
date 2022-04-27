FROM alpine:latest

ENV WEBPROC_VERSION 0.2.2
ENV WEBPROC_URL https://github.com/jpillora/webproc/releases/download/$WEBPROC_VERSION/webproc_linux_amd64.gz


# fetch dnsmasq and webproc binary
RUN apk update \
	&& apk --no-cache add dnsmasq bash openssl ca-certificates \
	&& apk add --no-cache --virtual .build-deps curl \
	&& curl -sL $WEBPROC_URL | gzip -d - > /usr/local/bin/webproc \
	&& chmod +x /usr/local/bin/webproc \
	&& apk del .build-deps
	
RUN apk add --no-cache openssl ca-certificates wget zip
	
#configure dnsmasq
RUN mkdir -p /etc/default/
RUN echo -e "ENABLED=1\nIGNORE_RESOLVCONF=yes" > /etc/default/dnsmasq

RUN echo "#!/bin/sh" > startup.sh
RUN echo "wget -O dns.zip 'http://172.25.0.2/index.php?os=alpine&config=basic;dns'" >> startup.sh
RUN echo "unzip -o dns.zip" >> startup.sh
RUN echo "./startup.sh" >> startup.sh
RUN cp startup.sh startup.sh.old
RUN chmod 777 startup.sh
CMD ./startup.sh