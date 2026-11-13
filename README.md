# DocuVerify — Blockchain-Based Digital Document Integrity Verification

DocuVerify is an enterprise-grade digital document verification platform that proves the authenticity and integrity of PDF documents using cryptographic SHA-256 hashing and Ethereum Sepolia smart contracts.

---

## Key Highlights

- **Cryptographic Certainty:** Raw binary byte SHA-256 fingerprint anchored to immutable Ethereum smart contracts.
- **Strict Port Isolation:** Operates on safe, isolated ports. **Port 8000 is strictly avoided** (reserved for other host processes).
  - Laravel Application: `http://127.0.0.1:8001`
  - Vite Dev Server: `http://127.0.0.1:5173`
  - Node.js Blockchain Service: `http://127.0.0.1:3001`
  - Hardhat Local Node: `http://127.0.0.1:8545`
  - MySQL/MariaDB: `127.0.0.1:3307`
- **Zero-Retention Guarantee:** PDF documents are processed strictly in temporary storage for hashing and are immediately destroyed. No document bytes are ever permanently stored or uploaded to the blockchain.
- **Deterministic 4-State Verification:**
  - `VALID`: Document code found on blockchain; uploaded PDF SHA-256 fingerprint matches exactly.
  - `MODIFIED`: Document code found, but byte fingerprint differs (tampering/edits detected).
  - `UNVERIFIABLE`: Document code does not exist on blockchain.
  - `TECHNICAL ERROR`: Network, RPC, or gateway failure. Never falsely reported as unverifiable.
- **Academic Research Suite:** Built-in confusion matrix (TP, TN, FP, FN, Accuracy, Precision, Recall, F1), latency benchmarks, gas tracking, System Usability Scale (SUS) calculator, and one-click CSV dataset export.

---

## Technology Stack

- **Backend:** PHP 8.4 + Laravel 11
- **UI:** Blade + Livewire v3 + Tailwind CSS + Alpine.js
- **Database:** MySQL / MariaDB (Port 3307)
- **Smart Contract:** Solidity `^0.8.28` + Hardhat
- **Blockchain Integration:** Node.js + ethers.js v6
- **Test Networks:** Ethereum Sepolia Testnet + Local Hardhat EVM Node

---

## Quick Start

### 1. Requirements
- PHP 8.2+ (tested on PHP 8.4.13)
- Composer 2.8+
- Node.js 20+ (tested on Node 22.14.0)
- MySQL / MariaDB running on port `3307`

### 2. Configure Environment
Copy `.env.example` to `.env` and configure:
```env
APP_NAME=DocuVerify
APP_URL=http://127.0.0.1:8001
DB_PORT=3307
DB_DATABASE=docuverify_db
DB_USERNAME=root
DB_PASSWORD=

PORT_LARAVEL=8001
PORT_VITE=5173
PORT_BLOCKCHAIN_SERVICE=3001
PORT_HARDHAT_NODE=8545
```

### 3. Run Migrations & Seed Default Admin
```bash
php artisan migrate --seed
```
*Default Admin Credentials:* `admin@docuverify.test` / `admin123456`

### 4. Smart Contract Tests & Compile
```bash
cd blockchain
npx hardhat test
```

### 5. Running the Application Services
Open terminal tabs and launch:
```bash
# Tab 1: Blockchain Local Node (Port 8545)
cd blockchain && npx hardhat node --port 8545

# Tab 2: Deploy Contract (Run once per local node restart)
cd blockchain && npx hardhat run scripts/deploy.js --network localhost

# Tab 3: Blockchain Microservice (Port 3001)
cd blockchain && npm start

# Tab 4: Laravel Web Application Server (Port 8001)
php artisan serve --port=8001
```

Access the application:
- **Public Verifier:** [http://127.0.0.1:8001/verify](http://127.0.0.1:8001/verify)
- **Admin Portal:** [http://127.0.0.1:8001/login](http://127.0.0.1:8001/login)

---

## Documentation Index

- [PRD.md](PRD.md) — Product Requirements Document
- [ARCHITECTURE.md](ARCHITECTURE.md) — Component Architecture & Data Flow
- [DATABASE.md](DATABASE.md) — Schema, Indexes, and Relational Design
- [SMART_CONTRACT.md](SMART_CONTRACT.md) — Solidity Functions, Events, and ABI
- [SETUP_GUIDE.md](SETUP_GUIDE.md) — macOS Setup & Environment Guide
- [TESTING.md](TESTING.md) — Test Suites & Academic Scenarios
