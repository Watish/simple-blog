FROM watish/alpine:base
RUN cd ~ && \
    wget https://wenda-1252906962.file.myqcloud.com/dist/swoole-cli-v5.0.1-linux-x64.tar.xz && \
    tar -xvf swoole-cli-v5.0.1-linux-x64.tar.xz && \
    chmod 777 swoole-cli && \
    mv swoole-cli /usr/bin/ && \
    rm *
RUN mkdir -p /opt/app/
VOLUME /opt/app/database/
COPY . /opt/app/
RUN chmod 777 /opt/app/ -R
EXPOSE 5950
ENTRYPOINT /opt/app/entrypoint.sh
