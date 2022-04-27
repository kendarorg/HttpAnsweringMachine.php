#docker build -t kendarssh .
#docker run -p 21000:2222 kendarssh
FROM alpine:latest
RUN apk update
RUN apk upgrade
RUN apk add --no-cache  bash vim
RUN apk add --no-cache openssh openrc wget unzip

RUN echo 'root:root' | chpasswd
RUN ssh-keygen -f /etc/ssh/ssh_host_rsa_key -N '' -t rsa
RUN mkdir -p /etc/ssh/run


EXPOSE 2222


RUN echo "#!/bin/sh" > startup.sh
RUN echo "wget -O ssh.zip 'http://172.25.0.2/index.php?os=alpine&config=basic;ssh'" >> startup.sh
RUN echo "unzip -o ssh.zip" >> startup.sh
RUN echo "./startup.sh" >> startup.sh
RUN cp startup.sh startup.sh.old
RUN chmod 777 startup.sh
CMD ./startup.sh