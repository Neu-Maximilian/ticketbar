FROM glpi/glpi:latest

# Disable SSL verification for PHP (NOT recommended for production)
RUN echo "[curl]" > /usr/local/etc/php/conf.d/zzz-disable-ssl.ini && \
    echo "curl.cainfo=" >> /usr/local/etc/php/conf.d/zzz-disable-ssl.ini && \
    echo "[openssl]" >> /usr/local/etc/php/conf.d/zzz-disable-ssl.ini && \
    echo "openssl.cafile=" >> /usr/local/etc/php/conf.d/zzz-disable-ssl.ini && \
    echo "openssl.capath=" >> /usr/local/etc/php/conf.d/zzz-disable-ssl.ini

# Add environment variable to disable SSL verification globally
ENV CURLOPT_SSL_VERIFYPEER=0
ENV CURLOPT_SSL_VERIFYHOST=0