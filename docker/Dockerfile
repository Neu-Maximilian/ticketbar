FROM glpi/glpi:latest

USER root

# Disable SSL verification for PHP (NOT recommended for production)
RUN echo "[curl]" > /usr/local/etc/php/conf.d/zzz-disable-ssl.ini && \
    echo "curl.cainfo=" >> /usr/local/etc/php/conf.d/zzz-disable-ssl.ini && \
    echo "[openssl]" >> /usr/local/etc/php/conf.d/zzz-disable-ssl.ini && \
    echo "openssl.cafile=" >> /usr/local/etc/php/conf.d/zzz-disable-ssl.ini && \
    echo "openssl.capath=" >> /usr/local/etc/php/conf.d/zzz-disable-ssl.ini

# Return to the original user used by GLPI
USER www-data

ENV CURLOPT_SSL_VERIFYPEER=0
ENV CURLOPT_SSL_VERIFYHOST=0
