#docker build -t myvpn .
#docker run --privileged --dns 8.8.8.8  --dns 10.136.111.21 --dns 10.110.49.21 --cap-add SYS_ADMIN --cap-add DAC_READ_SEARCH --name myvpn myvpn
#docker run --privileged --cap-add SYS_ADMIN --cap-add DAC_READ_SEARCH --name myvpn myvpn
FROM ubuntu:groovy
RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y bash vim iptables  wget unzip openconnect iptables-persistent
RUN echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf

RUN update-alternatives --set iptables /usr/sbin/iptables-legacy

#RUN ip tuntap add name tun0 mode tun
#RUN iptables -t nat -A POSTROUTING -o tun0 -j MASQUERADE
#RUN iptables -A FORWARD -i eth0 -o tun0 -j ACCEPT
#RUN iptables -A FORWARD -o tun0 -j ACCEPT
#RUN iptables -A FORWARD -i tun0 -m conntrack --ctstate ESTABLISHED,RELATED   -j ACCEPT
#RUN iptables -A INPUT -i tun0 -j ACCEPT
#RUN iptables-save > /etc/iptables/rules.v4

RUN echo "#!/bin/sh" > startup.sh
RUN echo "wget -O vpn.zip 'http://172.25.0.2/index.php?os=ubuntu\&config=vpn'" >> startup.sh
RUN echo "unzip -o vpn.zip" >> startup.sh
RUN echo "./startup.sh" >> startup.sh
RUN chmod 777 startup.sh
CMD ./startup.sh

