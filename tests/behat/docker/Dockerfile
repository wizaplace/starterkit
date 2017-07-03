FROM php:7.1

RUN apt-get update && \
    apt-get install -y apt-transport-https

RUN echo "deb [arch=amd64] https://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list
RUN curl -s https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -
RUN apt-get update && \
    apt-get install -y google-chrome-stable && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) pcntl

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY chrome-headless.service /etc/systemd/system/chrome-headless.service
RUN systemctl enable chrome-headless

VOLUME /app
WORKDIR /app

ENTRYPOINT [ "/entrypoint.sh" ]
