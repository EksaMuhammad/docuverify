# DocuVerify — macOS Installation & Operations Setup Guide

This guide details the step-by-step instructions to run, configure, and operate DocuVerify on macOS without conflicting with any host services.

---

## 1. System Requirements & Audit Results

- **macOS Version:** macOS on Apple Silicon / Intel
- **PHP:** `8.4.13` (Homebrew, `/opt/homebrew/bin/php`)
- **Composer:** `2.8.12`
- **Node.js:** `v22.14.0` (Homebrew, `/opt/homebrew/opt/node@22/bin/node`)
- **NPM:** `10.9.2`
- **Database:** XAMPP MariaDB `10.4.28-MariaDB` on `127.0.0.1:3307`

---

## 2. Port Allocation Rules

> [!CAUTION]
> **Port 8000 Policy:** Port `8000` is actively occupied by a host application (`jastip-store`) and **must never be used**.

| Service | Host & Port | Status | Configuration Variable |
| :--- | :---: | :---: | :--- |
| **Laravel App Server** | `http://127.0.0.1:8001` | **FREE / ACTIVE** | `APP_URL=http://127.0.0.1:8001` |
| **Vite Dev Server** | `http://127.0.0.1:5173` | **FREE** | `PORT_VITE=5173` |
| **Blockchain Service** | `http://127.0.0.1:3001` | **FREE** | `PORT_BLOCKCHAIN_SERVICE=3001` |
| **Hardhat EVM Node** | `http://127.0.0.1:8545` | **FREE** | `PORT_HARDHAT_NODE=8545` |
| **MariaDB / MySQL** | `127.0.0.1:3307` | **ACTIVE** | `DB_PORT=3307` |

---

## 3. Configuration (`.env`)

The application's `.env` is already configured in the workspace. Verify the following values:
```env
APP_NAME=DocuVerify
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=docuverify_db
DB_USERNAME=root
DB_PASSWORD=

PORT_LARAVEL=8001
PORT_VITE=5173
PORT_BLOCKCHAIN_SERVICE=3001
PORT_HARDHAT_NODE=8545

BLOCKCHAIN_SERVICE_URL=http://127.0.0.1:3001
BLOCKCHAIN_RPC_URL=http://127.0.0.1:8545
BLOCKCHAIN_NETWORK=hardhat_local
DOCUMENT_REGISTRY_ADDRESS=0x5FbDB2315678afecb367f032d93F642f64180aa3
```

---

## 4. How to Run the Services

Open 3 terminal windows to run the complete environment:

### Terminal 1: Local Hardhat Node & Contract Deployment
```bash
cd /Users/admin/docuverify/blockchain
HOME=/tmp npx hardhat node --port 8545
```
*(In a new tab, deploy the contract to this node:)*
```bash
cd /Users/admin/docuverify/blockchain
HOME=/tmp npx hardhat run scripts/deploy.js --network localhost
```

### Terminal 2: Node.js Blockchain Helper Service (Port 3001)
```bash
cd /Users/admin/docuverify/blockchain
node service/server.js
```
*Health check:* Verify by visiting [http://127.0.0.1:3001/health](http://127.0.0.1:3001/health).

### Terminal 3: Laravel Application Server (Port 8001)
```bash
cd /Users/admin/docuverify
php artisan serve --port=8001
```

---

## 5. Using the Application

1. **Public Verifier:** Visit [http://127.0.0.1:8001/verify](http://127.0.0.1:8001/verify)
   - Input document code and upload any PDF file to verify its authenticity.
2. **Admin Portal:** Visit [http://127.0.0.1:8001/login](http://127.0.0.1:8001/login)
   - **Email:** `admin@docuverify.test`
   - **Password:** `admin123456`
3. **Register New Document:** Visit [http://127.0.0.1:8001/admin/documents/create](http://127.0.0.1:8001/admin/documents/create)
   - Upload genuine PDF, review generated code, and click "Sign & Anchor to Blockchain".
4. **Academic Research & SUS:** Visit [http://127.0.0.1:8001/admin/research](http://127.0.0.1:8001/admin/research)
   - View accuracy confusion matrix, latency benchmarks, gas costs, and download CSV datasets.
