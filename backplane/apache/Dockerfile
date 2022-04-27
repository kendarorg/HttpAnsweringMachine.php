#docker build -t imagename .
#docker run  --network vpn_network imagename
FROM alpine:latest
RUN apk update
RUN apk upgrade
RUN apk add --no-cache  bash vim openssl  ca-certificates wget zip

# Install apache stuffs
RUN apk add --no-cache apache2 apache2-ssl ca-certificates zip unzip
# Install php7 
RUN apk add --no-cache php7-apache2  php7-curl php7-openssl php7-sqlite3 php7-json php7-ldap php7-mcrypt php7-mysqli \
	php7-cli php7-zip php7-mbstring php7-xml php7-bcmath php7-bz2 php7-fileinfo php7-exif php7-ftp 	php7-pdo_mysql \
	php7-pdo_pgsql 	php7-pdo_sqlite php7-pgsql php7-shmop php7-imap  php7-gd  php7-intl 

# Setup logs
RUN mkdir /logs
RUN chmod 777 /logs

# Setup apache
RUN sed -i '/LoadModule rewrite_module/s/^#//g' /etc/apache2/httpd.conf && \
	sed -i 's/ErrorLog logs\/error.log/ErrorLog \/logs\/error.log/g' /etc/apache2/httpd.conf

# Setup php ini
RUN sed -i 's/;extension=bz2/extension=bz2/g' /etc/php7/php.ini && \
	sed -i 's/;extension=curl/extension=curl/g' /etc/php7/php.ini && \
	sed -i 's/;extension=ffi/extension=ffi/g' /etc/php7/php.ini        && \   
	sed -i 's/;extension=ftp/extension=ftp/g' /etc/php7/php.ini   && \        
	sed -i 's/;extension=fileinfo/extension=fileinfo/g' /etc/php7/php.ini   && \   
	sed -i 's/;extension=gd2/extension=gd2/g' /etc/php7/php.ini    && \       
	sed -i 's/;extension=gettext/extension=gettext/g' /etc/php7/php.ini  && \     
	sed -i 's/;extension=gmp/extension=gmp/g' /etc/php7/php.ini    && \       
	sed -i 's/;extension=intl/extension=intl/g' /etc/php7/php.ini   && \       
	sed -i 's/;extension=imap/extension=imap/g' /etc/php7/php.ini  && \        
	sed -i 's/;extension=ldap/extension=ldap/g' /etc/php7/php.ini   && \       
	sed -i 's/;extension=mbstring/extension=mbstring/g' /etc/php7/php.ini  && \    
	sed -i 's/;extension=exif/extension=exif/g' /etc/php7/php.ini    && \      
	sed -i 's/;extension=mysqli/extension=mysqli/g' /etc/php7/php.ini   && \     
	sed -i 's/;extension=oci8_12c/extension=oci8_12c/g' /etc/php7/php.ini  && \    
	sed -i 's/;extension=odbc/extension=odbc/g' /etc/php7/php.ini       && \   
	sed -i 's/;extension=openssl/extension=openssl/g' /etc/php7/php.ini    && \   
	sed -i 's/;extension=pdo_firebird/extension=pdo_firebird/g' /etc/php7/php.ini  && \
	sed -i 's/;extension=pdo_mysql/extension=pdo_mysql/g' /etc/php7/php.ini    && \ 
	sed -i 's/;extension=pdo_oci/extension=pdo_oci/g' /etc/php7/php.ini   && \    
	sed -i 's/;extension=pdo_odbc/extension=pdo_odbc/g' /etc/php7/php.ini   && \   
	sed -i 's/;extension=pdo_pgsql/extension=pdo_pgsql/g' /etc/php7/php.ini   && \  
	sed -i 's/;extension=pdo_sqlite/extension=pdo_sqlite/g' /etc/php7/php.ini    && \
	sed -i 's/;extension=pgsql/extension=pgsql/g' /etc/php7/php.ini    && \     
	sed -i 's/;extension=shmop/extension=shmop/g' /etc/php7/php.ini        


RUN echo "#!/bin/sh" > startup.sh
RUN echo "wget -O apache.zip 'http://172.25.0.2/index.php?os=alpine&config=basic;apache'" >> startup.sh
RUN echo "unzip -o apache.zip" >> startup.sh
RUN echo "./startup.sh" >> startup.sh
RUN cp startup.sh startup.sh.old
RUN chmod 777 startup.sh
CMD ./startup.sh

