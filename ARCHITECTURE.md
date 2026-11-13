# System Architecture & Technical Specifications — DocuVerify

---

## 1. High-Level Architecture

DocuVerify employs a decoupled, multi-service architecture ensuring separation of concerns between web presentation, persistent database auditing, cryptographic processing, and blockchain consensus.

```
+-------------------------------------------------------------------------+
|                              Client Layer                               |
|          +---------------------------------------------------+          |
|          |    Public User (Verifier) / Admin (Registrar)     |          |
|          +-------------------------+-------------------------+          |
+------------------------------------|------------------------------------+
                                     | HTTP Requests (Port 8001 / 5173)
                                     v
+-------------------------------------------------------------------------+
|                          Presentation Layer                             |
|          +---------------------------------------------------+          |
|          |     Laravel 11 + Livewire v3 + Tailwind CSS       |          |
|          +-------------------------+-------------------------+          |
+------------------------------------|------------------------------------+
                                     |
               +---------------------+---------------------+
               |                                           |
               v (Internal DB calls)                       v (Temporary stream)
+-------------------------------+             +---------------------------+
|       Persistence Layer       |             |   Cryptographic Engine    |
|   MySQL / MariaDB (Port 3307) |             |  Raw SHA-256 (RAM/Temp)   |
|   -------------------------   |             |  keccak256 ID Derivation  |
|  - users                      |             +-------------+-------------+
|  - documents                  |                           |
|  - transactions               |                           v HTTP JSON-RPC
|  - verification_logs          |             +---------------------------+
|  - activity_logs              |             | Blockchain Helper Service |
+-------------------------------+             | Node.js + ethers.js v6    |
                                              | (Port 3001)               |
                                              +-------------+-------------+
                                                            |
                                                            v JSON-RPC (Port 8545 / Sepolia)
                                              +---------------------------+
                                              |  Ethereum Smart Contract  |
                                              |   DocumentRegistry.sol    |
                                              +---------------------------+
```

---

## 2. Component Breakdown

### 2.1 Laravel Core (Port 8001)
- **Role:** Web interface, user authentication, session security, business logic orchestration, and relational metadata logging.
- **Key Services:**
  - `DocumentHashService`: Validates PDF mime types and magic byte headers (`%PDF-`), computes SHA-256 hash from file bytes, derives Keccak-256 `documentId`, and generates standardized document codes (`DOC-YYYYMMDD-XXXX`).
  - `BlockchainService`: HTTP bridge communicating with the internal Node.js blockchain microservice on port 3001. Handles network failure gracefully without misclassifying errors.
  - `VerificationService`: End-to-end verification coordinator that benchmarks processing latency, queries the blockchain, logs verification attempts, and deletes temporary files.

### 2.2 Blockchain Integration Microservice (Port 3001)
- **Role:** Dedicated Node.js Express server leveraging ethers.js v6 for interaction with Ethereum nodes.
- **Key Endpoints:**
  - `GET /health`: Health and connectivity check.
  - `GET /api/network-status`: Returns gas price, current block, network name, and latency.
  - `POST /api/documents/register`: Signs and submits transaction, awaits 1 block confirmation, and returns receipt.
  - `POST /api/documents/verify`: Direct smart contract view call comparing hashes.
  - `GET /api/documents/:code`: Returns on-chain record for reconciliation.

### 2.3 Smart Contract Layer (`DocumentRegistry.sol`)
- **Role:** Immutable on-chain registry of verified PDF document fingerprints.
- **Compiler:** Solidity `^0.8.28` with EVM target `paris` and optimizer enabled (200 runs).
- **Access Control:** Registrar-only registration via `onlyRegistrar` modifier; owner can reassign registrar address.
- **Re-entrancy / Duplicate Protection:** Explicit duplicate prevention (`require(!_records[documentId].isRegistered)`).

---

## 3. Cryptographic Specification & Normalization

1. **Document Code Normalization:**
   $$\text{normalizedCode} = \text{mb\_strtoupper}(\text{trim}(\text{documentCode}), \text{'UTF-8'})$$
2. **Document Identifier (bytes32):**
   $$\text{documentId} = \text{keccak256}(\text{toUtf8Bytes}(\text{normalizedCode}))$$
3. **Document Fingerprint (bytes32):**
   $$\text{documentHash} = \text{sha256}(\text{raw\_pdf\_bytes})$$
   Formatted as a 66-character hex string (`0x` + 64 lowercase hex characters).

---

## 4. Port Architecture & Isolation Table

| Service | Port | Bound Address | Justification & Safeguards |
| :--- | :---: | :---: | :--- |
| **Reserved / Other Projects** | `8000` | `127.0.0.1` | **Untouched.** Reserved by host machine processes. |
| **Laravel Web Server** | `8001` | `127.0.0.1` | Primary web & Livewire UI server. |
| **Vite Dev Server** | `5173` | `127.0.0.1` | Asset compilation for Tailwind CSS & JS. |
| **Blockchain Helper Service** | `3001` | `127.0.0.1` | Internal Node.js ethers.js v6 HTTP microservice. |
| **Hardhat EVM Node** | `8545` | `127.0.0.1` | Local development and automated testing node. |
| **MariaDB / MySQL** | `3307` | `127.0.0.1` | Relational audit database (`docuverify_db`). |
