#docker build -t browser .
#docker run -p 5900:5900 -e VNC_SERVER_PASSWORD=password --user apps --privileged browser
#You can now start Chrome by right clicking the desktop and selecting:
#Applications > Network > Web Browsing > Google Chrome
FROM ubuntu:trusty

RUN apt-get update; apt-get clean

# Add a user for running applications.
RUN useradd apps
RUN mkdir -p /home/apps && chown apps:apps /home/apps

# Install x11vnc.
RUN apt-get install -y x11vnc  xvfb fluxbox  wget wmctrl bash vim openssl  ca-certificates

# Install the certificate root
COPY conf/ca.crt /usr/local/share/ca-certificates/
RUN chmod 655 /usr/local/share/ca-certificates/ca.crt
RUN update-ca-certificates

# Set the Chrome repo.
RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - \
    && echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list
# Install Chrome.
RUN apt-get update && apt-get -y install google-chrome-stable

COPY bootstrap.sh /

CMD '/bootstrap.sh'