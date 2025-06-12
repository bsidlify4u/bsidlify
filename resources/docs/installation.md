# Installation

## System Requirements

- PHP >= 8.2
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

## Installation Methods

### Via Composer Create-Project

```bash
composer create-project bsidlify/bsidlify my-project
cd my-project
php bsidlify key:generate
```

### Clone Repository

```bash
git clone https://github.com/bsidlify4u/bsidlify.git my-project
cd my-project
composer install
cp .env.example .env
php bsidlify key:generate
```

## Quick Start Guide

### Start Development Server

```bash
php bsidlify serve
```

### Run Development Environment with All Services

```bash
composer run dev
```

This starts concurrent processes:
- PHP development server
- Queue worker
- Log monitor
- Vite development server
