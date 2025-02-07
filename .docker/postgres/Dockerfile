FROM postgres:16-alpine

# Install build dependencies
RUN apk add --no-cache \
    build-base \
    clang \
    llvm \
    git \
    postgresql-dev

# Clone and install pgvector
RUN cd /tmp \
    && git clone --branch v0.8.0 https://github.com/pgvector/pgvector.git \
    && cd pgvector \
    && make clean \
    && make \
    && make install \
    && cd .. \
    && rm -rf pgvector

# Cleanup build dependencies
RUN apk del build-base clang llvm git postgresql-dev
